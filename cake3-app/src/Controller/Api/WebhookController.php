<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Entity\User;
use App\Utility\Weather;
use Cake\Core\Configure;
use Cake\I18n\Time;
use TelegramBot\Api\BotApi;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 * @property \App\Model\Table\CitiesTable $Cities
 *
 * @method User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WebhookController extends AppController
{

    public $modelClass = 'Users';

    public function initialize(): void
    {
        parent::initialize();

        $this->loadModel('Cities');
    }

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

            $city = $this->Cities->getCity($this->request);

            if (empty($city)) {
                $bot->sendMessage($user->chat_id, 'City not found, try again');

                return;
            }

            $owm = new Weather(Configure::read('OpenWeather.api_key'));
            $forecast = $owm->_getForecast($city->city_id, $user->language_code);

            $weatherUpdatedText = $owm->getWeatherUpdatedMessage($forecast);
            $weatherUpdatedMessage = $bot->sendMessage($user->chat_id, $weatherUpdatedText);

            $dailyForecastText = $owm->getDailyForecastMessage($forecast);
            $dailyForecastMessage = $bot->sendMessage($user->chat_id, $dailyForecastText);

            $currentWeatherText = $owm->getCurrentWeatherMessage($forecast);
            $currentWeatherMessage = $bot->sendMessage($user->chat_id, $currentWeatherText);

            $this->Users->patchEntity($user, [
                'city_id' => $city->city_id,
                'tz' => (int) $forecast->city->timezone->getName(),
                'weather_updated_message_id' => $weatherUpdatedMessage->getMessageId(),
                'forecast_message_id' => $dailyForecastMessage->getMessageId(),
                'weather_message_id' => $currentWeatherMessage->getMessageId(),
                'last_updated_forecast' => Time::now()->timestamp,
                'last_updated_weather' => Time::now()->timestamp,
            ]);
            $this->Users->saveOrFail($user);

        } catch (\Throwable $e) {

            throw $e;
        }
    }
}
