<?= includeTemplate('categories-navigation.php', ['categories' => $categories]) ?>
<form class="form container <?= $errors !== [] ? 'form--invalid' : ''?>" action="/login.php" method="post"> <!-- form--invalid -->
    <h2>Вход</h2>
    <?php if (isset($errors['auth'])): ?>
        <div class='form__error form__error--top'>
            <?= esc($errors['auth']) ?>
        </div>
    <?php endif ?>
    <div class="form__item <?= getErrorClassname($errors, 'email') ?>">
        <label for="email">E-mail <sup>*</sup></label>
        <input id="email" type="text" name="email" placeholder="Введите e-mail" value="<?= esc($formData['email'] ?? '')?>">
        <span class="form__error"><?= esc($errors['email'] ?? '') ?></span>
    </div>
    <div class="form__item form__item--last <?= getErrorClassname($errors, 'password') ?>">
        <label for="password">Пароль <sup>*</sup></label>
        <input id="password" type="password" name="password" placeholder="Введите пароль" value="<?= esc($formData['password'] ?? '')?>">
        <span class="form__error"><?= esc($errors['password'] ?? '') ?></span>
    </div>
    <button type="submit" class="button">Войти</button>
</form>
