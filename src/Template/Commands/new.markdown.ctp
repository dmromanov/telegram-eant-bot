[<?php echo $user->full_name; ?>](tg://user?id=<?php echo $user->id; ?>) хочет организовать мероприятие
<?php echo $message; ?>

<?php if (!empty($votes)): ?>
    <?php echo implode(PHP_EOL, $votes); ?>
<?php endif; ?>
