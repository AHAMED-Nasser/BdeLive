<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Articles;

/**
 * ArticlesController - Alias for ListArticlesController
 *
 * This controller serves as a routing alias to provide a clean URL:
 * index.php?page=articles → ArticlesController → ListArticlesController
 *
 * @package App\Modules\Controllers\Articles
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @see ListArticlesController For the actual implementation
 */
class ArticlesController extends ListArticlesController
{
    // Inherit all functionality from ListArticlesController
    // No additional code needed - this is a pure alias
}
