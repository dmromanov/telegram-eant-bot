<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Cake\Utility\Text;

/**
 * User Entity
 *
 * @property string $id
 * @property string $chat_id
 * @property string $firstname
 * @property string $lastname
 * @property string $username
 * @property \Cake\I18n\FrozenTime $last_activity
 * @property int $organized_events
 * @property string $lang
 * @property bool $is_bot
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 *
 * @property \App\Model\Entity\Chat $chat
 * @property \App\Model\Entity\Vote[] $votes
 */
class User extends Entity
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
        'firstname' => true,
        'lastname' => true,
        'username' => true,
        'last_activity' => true,
        'organized_events' => true,
        'lang' => true,
        'is_bot' => true,
        'created' => true,
        'modified' => true,
        'chat' => true,
        'votes' => true
    ];

    /**
     * @return string
     */
    protected function _getFullName(): string
    {
        $parts = [
            'firstName' => $this->firstname,
            'lastName' => $this->lastname,
        ];
        $parts = array_filter($parts);

        return implode(' ', $parts);
    }
}
