<?= includeTemplate('categories-navigation.php', ['categories' => $categories]) ?>
<form class="form form--add-lot container <?= $errors !== [] ? 'form--invalid' : '' ?>" action="add.php" method="post" enctype="multipart/form-data">
    <h2>Добавление лота</h2>
    <div class="form__container-two">
        <div class="form__item <?= getErrorClassname($errors, 'lot-name') ?>">
            <label for="lot-name">Наименование <sup>*</sup></label>
            <input id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота" value="<?= esc($itemData['lot-name'] ?? '') ?>">
            <span class="form__error"><?= esc($errors['lot-name'] ?? '') ?></span>
        </div>
        <div class="form__item <?= getErrorClassname($errors, 'category_id') ?>">
            <label for="category">Категория <sup>*</sup></label>
            <select id="category" name="category_id">
                <option value="">Выберите категорию</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?= esc($category['category_id']) ?>" <?= $category['category_id'] === ($itemData['category_id'] ?? '') ? 'selected' : '' ?>><?= esc($category['category_name']) ?></option>
                <?php endforeach ?>
            </select>
            <span class="form__error"><?= esc($errors['category_id'] ?? '') ?></span>
        </div>
    </div>
    <div class="form__item form__item--wide <?= getErrorClassname($errors, 'description') ?>">
        <label for="message">Описание <sup>*</sup></label>
        <textarea id="message" name="description" placeholder="Напишите описание лота"><?= esc($itemData['description'] ?? '') ?></textarea>
        <span class="form__error"><?= esc($errors['description'] ?? '') ?></span>
    </div>
    <div class="form__item form__item--file <?= getErrorClassname($errors, 'image') ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
            <input class="visually-hidden" name="image" type="file" id="lot-img" value="">
            <label for="lot-img">
                Добавить
            </label>
        </div>
        <span class="form__error"><?= esc($errors['image'] ?? '') ?></span>
    </div>
    <div class="form__container-three">
        <div class="form__item form__item--small <?= getErrorClassname($errors, 'lot-rate') ?>">
            <label for="lot-rate">Начальная цена <sup>*</sup></label>
            <input id="lot-rate" type="text" name="lot-rate" placeholder="0" value="<?= esc($itemData['lot-rate'] ?? '') ?>">
            <span class="form__error"><?= esc($errors['lot-rate'] ?? '') ?></span>
        </div>
        <div class="form__item form__item--small <?= getErrorClassname($errors, 'lot-step') ?>">
            <label for="lot-step">Шаг ставки <sup>*</sup></label>
            <input id="lot-step" type="text" name="lot-step" placeholder="0" value="<?= esc($itemData['lot-step'] ?? '') ?>">
            <span class="form__error"><?= esc($errors['lot-step'] ?? '') ?></span>
        </div>
        <div class="form__item <?= getErrorClassname($errors, 'lot-date') ?>">
            <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
            <input class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД" value="<?= esc($itemData['lot-date'] ?? '') ?>">
            <span class="form__error"><?= esc($errors['lot-date'] ?? '') ?></span>
        </div>
    </div>
    <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
    <button type="submit" class="button">Добавить лот</button>
</form>
