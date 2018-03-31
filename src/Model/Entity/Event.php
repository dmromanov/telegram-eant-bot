<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Event Entity
 *
 * @property string $id
 * @property string $chat_id
 * @property string $author_id
 * @property string $id_message
 * @property string $title
 * @property \Cake\I18n\FrozenTime $date
 * @property int $min_responses
 * @property string $geopoint
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Chat $chat
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Vote[] $votes
 */
class Event extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'chat_id' => true,
        'author_id' => true,
        'id_message' => true,
        'title' => true,
        'date' => true,
        'min_responses' => true,
        'geopoint' => true,
        'created' => true,
        'modified' => true,
        'chat' => true,
        'user' => true,
        'votes' => true
    ];
}
