[<?php echo $user->firstname; ?>](tg://user?id=<?php echo $user->id; ?>) хочет организовать кое-что интересное! 🎉
<?php echo $message; ?>

<?php if (!empty($votes)): ?>
    <?php echo implode(PHP_EOL, $votes); ?>
<?php endif; ?>
