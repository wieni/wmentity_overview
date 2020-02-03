wmentity_overview
======================

[![Latest Stable Version](https://poser.pugx.org/wieni/wmentity_overview/v/stable)](https://packagist.org/packages/wieni/wmentity_overview)
[![Total Downloads](https://poser.pugx.org/wieni/wmentity_overview/downloads)](https://packagist.org/packages/wieni/wmentity_overview)
[![License](https://poser.pugx.org/wieni/wmentity_overview/license)](https://packagist.org/packages/wieni/wmentity_overview)

> Improved EntityListBuilders with support for paging, table sorting,
> table dragging, filtering, database queries and more.

## Why?
At Wieni, we're not a big fan of the
[Views](https://www.drupal.org/docs/8/core/modules/views) module and for
a couple of reasons:
- we're programmers, we don't like to create functionality by clicking
  through the Drupal interface and especially not the bloated Views
  interface
- we prefer to write our own database or entity queries, it gives us
  more flexibility when filtering and including non-entity field data
  and it makes it easier to optimize queries

That's why a couple of years ago we decided to disable the module
altogether and we ended up with the
[EntityListBuilder](https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Entity%21EntityListBuilder.php/class/EntityListBuilder/8.2.x)-based
listings.

Those proved to be lacking in functionality and user friendliness soon
enough, which is how we ended up creating this module: the perfect
middle ground between Views and EntityListBuilder. It offers all
features of the latter, plus the following:

- **Exposed filtering** with a custom form
- **Behind-the-scenes filtering** using custom database queries
- **Table sorting**, configurable on a per-column basis
- **Override built-in entity listings** with a custom one, on an entity
  type or bundle level
- **Override any route** with a custom entity listing

## Installation

This package requires PHP 7.1 and Drupal 8 or higher. It can be
installed using Composer:

```bash
 composer require wieni/wmentity_overview
```

## How does it work?
### Create an overview
Overviews are Drupal plugins with the
[`@OverviewBuilder`](src/Annotation/OverviewBuilder.php) annotation.
Every annotation needs at least the `entity_type` and `id` parameters to
function.

There are three base classes you can choose from:
- [`OverviewBuilderBase`](src/OverviewBuilder/OverviewBuilderBase.php): the most basic entity overview with support
  for database queries, paging and table sort
- [`FilterableOverviewBuilderBase`](src/OverviewBuilder/FilterableOverviewBuilderBase.php):
  an entity overview combining the base functionality with an exposed
  filter form
- [`DraggableOverviewBuilderBase`](src/OverviewBuilder/DraggableOverviewBuilderBase.php):
  an entity overview with a draggable, re-orderable table, but without
  support for paging or filtering

#### Example
```php
<?php

namespace Drupal\yourmodule\Plugin\EntityOverview;

/**
 * @OverviewBuilder(
 *     entity_type = "node",
 * )
 */
class NodeOverview extends FilterableOverviewBuilderBase
{
}
```

### Override en existing entity listing
You can override the default entity listing with a custom overview by
adding the `override` parameter to the annotation. In case the entity
type is not recognised by this module, you can add the `route_name` and
pass the route name of the entity listing instead.

It is also possible to override the entity listing only when a certain
combination of filters is active. This way, you could for example add
extra filters or table columns when your overview is filtered by a
certain bundle.

#### Examples
```php
<?php

namespace Drupal\yourmodule\Plugin\EntityOverview;

/**
 * @OverviewBuilder(
 *     id = "node",
 *     entity_type = "node",
 *     override = true,
 * )
 */
class NodeOverview extends FilterableOverviewBuilderBase
{
}
```

```php
<?php

namespace Drupal\yourmodule\Plugin\EntityOverview;

/**
 * @OverviewBuilder(
 *     id = "redirect",
 *     entity_type = "redirect",
 *     route_name = "redirect.list",
 * )
 */
class RedirectOverview extends FilterableOverviewBuilderBase
{
}
```

```php
<?php

namespace Drupal\yourmodule\Plugin\EntityOverview;

/**
 * @OverviewBuilder(
 *     id = "node.article",
 *     entity_type = "node",
 *     override = true,
 *     filters = {
 *         "type" = "article",
 *     },
 * )
 */
class ArticleOverview extends NodeOverview
{
}
```

### Render an overview
When you create an overview without overriding an existing route, you
will have to render it somewhere manually.

Creating an instance of an entity overview is done the same way as other
Drupal plugins, by using the `createInstance` method of
[`OverviewBuilderManager`](src/OverviewBuilder/OverviewBuilderManager.php).

Another option is adding `_entity_overview` to the `defaults` section of
your route definition, with as value the plugin id.

#### Example
```yaml
yourmodule.content_overview.article:
    path: '/admin/content/article'
    defaults:
        _entity_overview: 'node.article'
        _title: 'Articles'
    requirements:
        _permission: 'administer nodes'
    options:
        _admin_route: TRUE
```

### Filter storages
Entity overviews with exposed filter forms need a place to (temporarily)
store their filter values. That's where filter storages come to play:
an abstraction in the way these values are stored.

By default, two storage methods are included: `query`, which stores
values as query parameters in the URL and `session`, which stores values
in the session storage.

Custom storage methods can be added by creating a Drupal plugin with the
[`@FilterStorage`](src/Annotation/FilterStorage.php) annotation and an
`id` parameter, implementing
[FilterStorageInterface](src/FilterStorage/FilterStorageInterface.php)
and optionally extending
[`FilterStorageBase`](src/FilterStorage/FilterStorageBase.php).

The default storage method is `query`, but this can be changed by adding
a `filter_storage` parameter to `@OverviewBuilder` annotations.

### Hooks and events
#### `hook_entity_overview_alter`
This hook is only called when using overrides or when using the
`_entity_overview` default in routes. An event equivalent to the hook is
also provided:
[`WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER`](src/WmEntityOverviewEvents.php)

##### Examples
```php
<?php

use Drupal\wmentity_overview\Annotation\OverviewBuilder;

function yourmodule_entity_overview_alter(OverviewBuilder $definition, array $overview)
{
    if (!empty($overview['form'])) {
        $overview['form']['#attributes']['class'][] = 'custom-entity-overview__form';
    }

    $overview['table']['#attributes']['class'][] = 'custom-entity-overview__table';
}
```

```php
<?php

namespace Drupal\yourmodule\EventSubscriber;

use Drupal\wmentity_overview\Event\EntityOverviewAlterEvent;
use Drupal\wmentity_overview\WmEntityOverviewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityOverviewSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        $events[WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTER][] = ['onAlter'];

        return $events;
    }

    public function onAlter(EntityOverviewAlterEvent $event): void
    {
        $overview = &$event->getOverview();

        if (!empty($overview['form'])) {
            $overview['form']['#attributes']['class'][] = 'custom-entity-overview__form';
        }
    
        $overview['table']['#attributes']['class'][] = 'custom-entity-overview__table';
    }
}
```

#### `hook_entity_overview_alternatives_alter`
This hook is only called in the
[`OverviewBuilderManager::getAlternatives`](src/OverviewBuilder/OverviewBuilderManager.php)
method. An event equivalent to the hook is also provided:
[`WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTERNATIVES_ALTER`](src/WmEntityOverviewEvents.php)

#### Example
```php
<?php

namespace Drupal\yourmodule\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\wmentity_overview\Event\EntityOverviewAlternativesAlterEvent;
use Drupal\wmentity_overview\OverviewBuilder\OverviewBuilderManager;
use Drupal\wmentity_overview\WmEntityOverviewEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityOverviewAlternativesSubscriber implements EventSubscriberInterface
{
    /** @var OverviewBuilderManager */
    protected $overviewBuilders;
    /** @var RouteMatchInterface */
    protected $routeMatch;

    public function __construct(
        OverviewBuilderManager $overviewBuilders,
        RouteMatchInterface $routeMatch
    ) {
        $this->overviewBuilders = $overviewBuilders;
        $this->routeMatch = $routeMatch;
    }

    public static function getSubscribedEvents()
    {
        $events[WmEntityOverviewEvents::ENTITY_OVERVIEW_ALTERNATIVES_ALTER][] = ['onTaxonomyAlternativesAlter'];

        return $events;
    }

    /**
     * Since taxonomy has a per-bundle overview, we get the bundle from
     * the route parameters and use it to add more possible alternatives.
     */
    public function onTaxonomyAlternativesAlter(EntityOverviewAlternativesAlterEvent $event): void
    {
        if (!$vocabulary = $this->routeMatch->getParameter('taxonomy_vocabulary')) {
            return;
        }

        if ($event->getDefinition()->getEntityTypeId() !== 'taxonomy_term') {
            return;
        }

        $filters = ['vid' => $vocabulary->id()];
        $alternatives = array_merge(
            $event->getAlternatives(),
            $this->overviewBuilders->getAlternativesByFilters($event->getDefinition(), $filters)
        );

        $event->setAlternatives($alternatives);
    }
}
```
## Changelog
All notable changes to this project will be documented in the
[CHANGELOG](CHANGELOG.md) file.

## Security
If you discover any security-related issues, please email
[security@wieni.be](mailto:security@wieni.be) instead of using the issue
tracker.

## License
Distributed under the MIT License. See the [LICENSE](LICENSE.md) file
for more information.
