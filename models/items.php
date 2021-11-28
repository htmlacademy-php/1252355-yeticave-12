<?php
/**
 * Получает лот по его id
 * @param   mysqli      $db      Объект с базой данных
 * @param   integer     $itemId  id лота
 * @return  array|null           Ассоциативный массив с данными лота
 */
function getItem(mysqli $db, int $itemId): ?array
{
    $sql = "
        SELECT i.item_name,
               i.item_description,
               i.item_initial_price,
               i.item_bid_step,
               i.seller_id,
               i.item_image,
               i.item_date_expire,

               COALESCE(
                  (SELECT MAX(b.bid_price)
                     FROM bids AS b
                    WHERE i.item_id = b.item_id),
                  i.item_initial_price
               ) AS current_price,

               c.category_name
          FROM items AS i
               INNER JOIN categories AS c
               ON i.category_id = c.category_id
         WHERE i.item_id = ?
    ";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $itemId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Получает действующие лоты, отсортированные от новых к старым
 * @param   mysqli      $db  Объект с базой данных
 * @return  array|null       Ассоциативный массив с данными лотов
 */
function getNewItems(mysqli $db): ?array
{
    $sql = "
        SELECT i.item_id,
               i.item_name,
               i.item_image,
               i.item_date_expire,
               COALESCE(b.top_bid, i.item_initial_price) AS current_price,
               COALESCE(b.bids_count, 0) AS bids_count,
               c.category_name
          FROM items AS i
               INNER JOIN categories AS c
               ON i.category_id = c.category_id
               LEFT JOIN (
                   SELECT item_id,
                          MAX(bid_price) AS top_bid,
                          COUNT(bid_id) AS bids_count
                     FROM bids
                    GROUP BY item_id
               ) AS b
               ON i.item_id = b.item_id
         WHERE item_date_expire > NOW()
         ORDER BY item_date_added DESC
    ";
    return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Добавляет в базу данных запись с новым лотом
 * @param   mysqli   $db        Объект с базой данных
 * @param   array    $itemData  Данные лота
 * @return  integer             id нового лота
 */
function insertItem(mysqli $db, array $itemData): int
{
    $sql = "
        INSERT INTO items (
            item_name,
            item_description,
            item_image,
            item_initial_price,
            item_bid_step,
            seller_id,
            category_id,
            item_date_expire
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param(
        "ssssssss",
        $itemData['lot-name'],
        $itemData['description'],
        $itemData['image']['webPath'],
        $itemData['lot-rate'],
        $itemData['lot-step'],
        $itemData['user_id'],
        $itemData['category_id'],
        $itemData['lot-date']
    );
    $stmt->execute();

    return $db->insert_id;
}

/**
 * Находит лоты с учётом максимального числа элементов на странице и смещения выборки
 * @param   mysqli      $db              Объект с базой данных
 * @param   string      $searchString    Значение поисковой строки
 * @param   integer     $pageItemsLimit  Максимальное количество элементов на странице
 * @param   integer     $offset          Смещение выборки
 * @return  array|null                   Найденные лоты
 */
function searchItems(mysqli $db, string $searchString, int $pageItemsLimit, int $offset): array
{
    $sql = "
        SELECT i.item_id,
               i.item_name,
               i.item_image,
               i.item_date_expire,
               COALESCE(b.top_bid, i.item_initial_price) AS current_price,
               COALESCE(b.bids_count, 0) AS bids_count,
               c.category_name
          FROM items AS i
               INNER JOIN categories AS c
               ON i.category_id = c.category_id
               LEFT JOIN (
                   SELECT item_id,
                          MAX(bid_price) AS top_bid,
                          COUNT(bid_id) AS bids_count
                     FROM bids
                    GROUP BY item_id
               ) AS b
               ON i.item_id = b.item_id
         WHERE item_date_expire > NOW()
           AND MATCH(i.item_name, i.item_description) AGAINST(?)
         LIMIT ? OFFSET ?
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("sss", $searchString, $pageItemsLimit, $offset);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Считает общее количество лотов, подходящих по условиям поиска
 * @param   mysqli   $db            Объект с базой данных
 * @param   string   $searchString  Значение поисковой строки
 * @return  integer                 Количество лотов, подходящих по условиям поиска
 */
function countFoundItems(mysqli $db, string $searchString): int
{
    $sql = "
        SELECT COUNT(*) as foundItemsCount
          FROM items
         WHERE item_date_expire > NOW()
           AND MATCH(item_name, item_description) AGAINST(?)
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("s", $searchString);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['foundItemsCount'];
}

/**
 * Получает победителей для истекших лотов без победителей
 * @param   mysqli  $db  Объект с базой данных
 * @return  array        Победители
 */
function getNewWinners(mysqli $db): array
{
    $sql = "
        SELECT b.user_id,
               u.user_email,
               u.user_name,
               i.item_id,
               i.item_name
          FROM items AS i
               INNER JOIN bids  AS b ON i.item_id = b.item_id
               INNER JOIN users AS u ON b.user_id = u.user_id
         WHERE i.item_date_expire <= NOW()
           AND i.winner_id IS NULL
           AND b.bid_price = (SELECT MAX(b1.bid_price) FROM bids AS b1 WHERE b1.item_id = i.item_id)
    ";

    return $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

/**
 * Задаёт победителя лоту
 * @param   mysqli   $db        Объект с базой данных
 * @param   integer  $itemId    id лота
 * @param   integer  $winnerId  id победителя
 * @return  boolean             true, если было обновлено новыми данными, иначе false
 */
function setItemWinner(mysqli $db, int $itemId, int $winnerId): bool
{
    $sql = "
        UPDATE items SET winner_id = ? WHERE item_id = ?
    ";

    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $winnerId, $itemId);
    $stmt->execute();
    return (bool) $db->affected_rows;
}
