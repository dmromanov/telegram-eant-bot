<?php if (empty($activists) && empty($inactive)): ?>
У меня недостаточно информации.
<?php return; endif; ?>

На текущий момент, у меня есть следующая информация:
<?php if (!empty($activists)): ?>

Активисты (создали хотя бы одно событие):
<?php foreach ($activists as $user): ?>
    <?php echo $user->fullName, PHP_EOL; ?>
<?php endforeach; ?>
<?php endif; ?>
<?php if (!empty($inactive)): ?>

Неактивные пользователи:
<?php foreach ($inactive as $user): ?>
    <?php echo $user->fullName, PHP_EOL; ?>
<?php endforeach; ?>
<?php endif; ?>

Отчётный период: 3 месяца.
