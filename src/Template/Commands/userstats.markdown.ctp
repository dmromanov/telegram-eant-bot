<?php if (empty($activists) && empty($inactiv)): ?>
У меня недостаточно информации.
<?php endif; return; ?>

На текущий момент, у меня есть следующая информация:
<?php if (!empty($activists)): ?>

    Активисты (создали хотя бы одно событие):
    <?php echo implode(PHP_EOL, array_map(function (\App\Model\Entity\User $user) { return $user->fullName; }, $activists)); ?>
<?php endif; ?>
<?php if (!empty($inactive)): ?>

Неактивные пользователи:
    <?php echo implode(PHP_EOL, array_map(function (\App\Model\Entity\User $user) { return $user->fullName; }, $inactive)); ?>
<?php endif; ?>

Отчётный период: 3 месяца.
