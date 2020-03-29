<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Entity\User;
use App\Utility\Weather;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\ORM\TableRegistry;
use Cake\Utility\Text;
use TelegramBot\Api\BotApi;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 *
 * @method User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WebhookController extends AppController
{

    public $modelClass = 'Users';

    /**
     * @throws \Throwable
     */
    public function hook()
    {
        try {
            $bot = new BotApi(Configure::read('Bot.api_key'));
            $bot->setProxy('socks5://v3_279932456:yYvsvPT1@s5.priv.opennetwork.cc:1080');

            if ($this->request->getData('message.text') == '/start') {
                $bot->sendMessage($this->request->getData('message.chat.id'), 'Hi, just send me your city');

                return;
            }

            $user = $this->Users->getOrCreateUser($this->request);

            $Cities = TableRegistry::getTableLocator()->get('Cities');
            $city = $Cities->find()->where([
                'OR' => [
                    ['name' => $this->request->getData('message.text')],
                    ['name' => Text::transliterate($this->request->getData('message.text'))]
                ]
            ])
                ->first();

            if (empty($city)) {
                $bot->sendMessage($user->chat_id, 'Not found');

                return;
            }

            $bot->sendMessage($user->chat_id, $city->name);
            $user->city_id = $city->city_id;

            $owm = new Weather(Configure::read('OpenWeather.api_key'));
            $weatherMessage = $owm->getWeatherMessage($user->city_id, $user->language_code);
            $message = $bot->sendMessage($user->chat_id, $weatherMessage);

            $user->message_id = $message->getMessageId();
            $user->last_updated = Time::now()->timestamp;
            $this->Users->saveOrFail($user);

        } catch (\Throwable $e) {

            throw $e;
        }
    }
}
