<?php
/**
 * @var User $user
 * @var string $message
 * @var Query|Vote[]|CollectionInterface $votes
 */

use App\Model\Entity\User;
use App\Model\Entity\Vote;
use Cake\Collection\CollectionInterface;
use Cake\Database\Query;

?>
[<?= $user->fullName; ?>](tg://user?id=<?= $user->id; ?>) хочет организовать кое-что интересное! 🎉
*<?= $message; ?>*

<?php if (!empty($votes)): ?>
<?php foreach ($votes->select(['fullName', 'vote']) as $vote): ?>
    <?= $vote->user->fullName?>: <?= $this->Votes->format($vote->vote), PHP_EOL; ?>
<?php endforeach; ?>
<?php endif; ?>
