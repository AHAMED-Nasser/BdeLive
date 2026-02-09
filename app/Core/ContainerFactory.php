<?php

declare(strict_types=1);

namespace App\Core;

use App\Modules\Repositories\Interfaces\ArticleRepositoryInterface;
use App\Modules\Repositories\ArticleRepository;
use App\Modules\Repositories\Interfaces\EventRepositoryInterface;
use App\Modules\Repositories\EventRepository;

/**
 * ContainerFactory - Builds the DI container with default service bindings
 *
 * Keeps wiring logic in Core (next to Container), not in Config.
 * Config remains for environment (DB, keys, etc.).
 *
 * @package App\Core
 * @author BdeLive - Group 8
 * @version 1.0.0
 */
class ContainerFactory
{
    public static function create(): Container
    {
        $container = new Container();

        $container->set(ArticleRepositoryInterface::class, function (Container $c): ArticleRepository {
            return new ArticleRepository(Database::getInstance()->getConnection());
        });

        $container->set(EventRepositoryInterface::class, function (Container $c): EventRepository {
            return new EventRepository(Database::getInstance()->getConnection());
        });

        return $container;
    }
}
