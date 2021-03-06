<?php
require __DIR__ . '/initialize.php';
require __DIR__ . '/validators.php';

$categoryId = $_GET['category_id'] ?? null;
$allowedCategoriesIds = array_map(function($category) { return $category['category_id']; }, $categories);
$currentPage = (int) ($_GET['page'] ?? 1);

if (
    validateInArray($categoryId, $allowedCategoriesIds)
    || validateInt($currentPage)
    || validateNumberRange($currentPage, 1)
) {
    httpError($categories, $user, 404);
}

require __DIR__ . '/models/items.php';
$categoryItemsCount = countCategoryItems($db, $categoryId);
$pageItemsLimit = 9;

list ($pages, $offset) = initializePagination(
    $currentPage,
    $categoryItemsCount,
    $pageItemsLimit,
    'httpError',
    [$categories, $user, 404]
);

$qsParameters = ['category_id' => $categoryId, 'page' => $currentPage];
$categoryItems = getItems($db, $pageItemsLimit, $offset, null, $categoryId);
$categoryItems = includeCbResultsForEachElement($categoryItems, 'getRemainingTime', ['item_date_expire']);

$categoryData = array_merge(...array_filter($categories, function($category) use ($categoryId) {
    return $category['category_id'] === $categoryId;
}));
$categoryName = $categoryData['category_name'];

echo getHtml('lots-by-category.php', [
    'categories' => $categories,
    'pageAddress' => $_SERVER['PHP_SELF'],
    'qsParameters' => $qsParameters,
    'categoryId' => $categoryId,
    'categoryItems' => $categoryItems,
    'categoryName' => $categoryName,
    'pagesCount' => count($pages),
    'currentPage' => $currentPage,
    'pages' => $pages,
], $categories, $user, $categoryName);
