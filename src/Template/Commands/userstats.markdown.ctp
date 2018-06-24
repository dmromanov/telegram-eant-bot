<?php if (!iterator_count($activists)): ?>
У меня недостаточно информации.
<?php return; endif; ?>

На текущий момент, у меня есть следующая информация:
<?php if (iterator_count($activists)): ?>

Активисты (создали хотя бы одно событие):
<?php foreach ($activists as $user): ?>
    <?= $user->fullName, PHP_EOL; ?>
<?php endforeach; ?>
<?php endif; ?>
<?php if (iterator_count($activists)):?>

Неактивные пользователи:
_все остальные_
<?php endif; ?>

Отчётный период: 3 месяца.
