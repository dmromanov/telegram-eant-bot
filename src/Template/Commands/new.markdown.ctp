[<?php echo $user->fullName; ?>](tg://user?id=<?php echo $user->id; ?>) хочет организовать кое-что интересное! 🎉
*<?php echo $message; ?>*

<?php if (!empty($votes)): ?>
<?php foreach ($votes as $vote): ?>
    <?= $vote->user->fullName?>: <?= $this->Votes->format($vote->vote); ?>
<?php endforeach; ?>
<?php endif; ?>
