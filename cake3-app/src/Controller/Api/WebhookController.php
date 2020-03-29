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

            $user = $this->Users->getOrCreateUser($this->request);

            if ($this->request->getData('message.text') == '/start') {
                $bot->sendMessage($this->request->getData('message.chat.id'), 'Hi, just send me your city');

                return;
            }

            $Cities = TableRegistry::getTableLocator()->get('Cities');
            $city = $Cities->find()->where([
                'OR' => [
                    ['name' => $this->request->getData('message.text')],
                    ['name' => Text::transliterate($this->request->getData('message.text'))],
                    ['name' => Text::transliterate($this->request->getData('message.text'), 'Russian-Latin/BGN')]
                ]
            ])
                ->first();

            if (empty($city)) {
                $bot->sendMessage($user->chat_id, 'City not found, try again');

                return;
            }

            $bot->sendMessage($user->chat_id, $city->name);
            $user->city_id = $city->city_id;

            $owm = new Weather(Configure::read('OpenWeather.api_key'));
            $forecastMessage = $owm->getForecastMessage($user->city_id, $user->language_code);
            $weatherMessage = $owm->getWeatherMessage($user->city_id, $user->language_code);

            $forecastMessage = $bot->sendMessage($user->chat_id, $forecastMessage);
            $message = $bot->sendMessage($user->chat_id, $weatherMessage);

            $user->forecast_message_id = $forecastMessage->getMessageId();
            $user->weather_message_id = $message->getMessageId();

            $user->last_updated_forecast = Time::now()->timestamp;
            $user->last_updated_weather = Time::now()->timestamp;

            $this->Users->saveOrFail($user);

        } catch (\Throwable $e) {

            throw $e;
        }
    }
}
