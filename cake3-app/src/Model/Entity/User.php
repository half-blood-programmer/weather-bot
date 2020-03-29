<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property int $created
 * @property bool $available
 * @property string $first_name
 * @property string|null $username
 * @property string $language_code
 * @property bool $is_bot
 * @property int $user_id
 * @property int $chat_id
 * @property int|null $city_id
 * @property int|null $message_id
 * @property int|null $last_updated
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
        'created' => true,
        'available' => true,
        'first_name' => true,
        'username' => true,
        'language_code' => true,
        'is_bot' => true,
        'user_id' => true,
        'chat_id' => true,
        'city_id' => true,
        'message_id' => true,
        'last_updated' => true,
        'telegram' => true,
        'city' => true,
    ];
}
