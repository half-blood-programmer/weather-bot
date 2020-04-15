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
    public BotApi $bot;

    public function initialize(): void
    {
        parent::initialize();

        $this->loadModel('Cities');

        $this->bot = new BotApi(Configure::read('Bot.api_key'));

        if (true) {
            $this->bot->setProxy('socks5://v3_279932456:yYvsvPT1@s5.priv.opennetwork.cc:1080');
        }
    }

    /**
     * @throws \Throwable
     */
    public function hook() : void
    {
        try {

            $user = $this->Users->getOrCreateUser($this->request);

            if ($this->request->getData('message.text') == '/start') {
                $this->bot->sendMessage($this->request->getData('message.chat.id'), 'Hi, just send me your city');

                return;
            }

            $city = $this->Cities->getCity($this->request);

            if (empty($city)) {
                $this->bot->sendMessage($user->chat_id, 'City not found, try again');

                return;
            }

            $owm = new Weather(Configure::read('OpenWeather.api_key'));
            $forecast = $owm->getSimpleForecast($city->city_id, $user->language_code);

            $weatherUpdatedText = $owm->getWeatherUpdatedMessage($forecast);
            $weatherUpdatedMessage = $this->bot->sendMessage($user->chat_id, $weatherUpdatedText);

            $dailyForecastText = $owm->getDailyForecastMessage($forecast);
            $dailyForecastMessage = $this->bot->sendMessage($user->chat_id, $dailyForecastText);

            $currentWeatherText = $owm->getCurrentWeatherMessage($forecast);
            $currentWeatherMessage = $this->bot->sendMessage($user->chat_id, $currentWeatherText);

            $this->Users->patchEntity($user, [
                'city_id' => $city->city_id,
                'tz' => (int) $forecast->city->timezone->getName(),
                'weather_updated_message_id' => $weatherUpdatedMessage->getMessageId(),
                'daily_forecast_message_id' => $dailyForecastMessage->getMessageId(),
                'current_weather_message_id' => $currentWeatherMessage->getMessageId(),
                'last_updated_weather' => Time::now()->timestamp,
            ]);
            $this->Users->saveOrFail($user);

        } catch (\Throwable $e) {

            throw $e;
        }
    }
}
