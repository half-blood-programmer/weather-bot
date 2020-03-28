<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\Entity\User;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cmfcmf\OpenWeatherMap;
use Laminas\Diactoros\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use TelegramBot\Api\BotApi;

/**
 * Users Controller
 *
 * // * @property \App\Model\Table\UsersTable $Users
 *
 * // * @method User[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class WebhookController extends AppController
{

    public $modelClass = 'Users';

    /**
     * @api {post} api/users.json Register new account
     * @apiVersion 0.1.0
     * @apiName Register
     * @apiGroup Users
     * @apiPermission Public
     *
     * @apiDescription Returns user's basic information
     *
     * @apiParam (ApiPOSTParam) {String} email User's email
     * @apiParam (ApiPOSTParam) {String} name User's name
     * @apiParam (ApiPOSTParam) {String} password User's password
     *
     * @apiUse UsersPrimaryData
     *
     * @apiUse BadRequest
     * @apiUse NotFound
     * @apiUse Forbidden
     */
    public function hook()
    {
        try {
            $bot = new BotApi(Configure::read('Bot.api_key'));
            $bot->setProxy('socks5://v3_279932456:yYvsvPT1@s5.priv.opennetwork.cc:1080');

            Log::debug($this->request->getData('message.text'));

            if ($this->request->getData('message.text') == '/start') {
                $this->_start($bot);

                return;
            }

            $user = $this->Users->find()
                ->where(['user_id' => $this->request->getData('message.from.id')])
                ->first();

            if (!$user) {
                $user = $this->Users->newEntity([
                    'created' => Time::now()->timestamp,
                    'available' => true,
                    'first_name' => $this->request->getData('message.from.first_name'),
                    'username' => $this->request->getData('message.from.username'),
                    'language_code' => $this->request->getData('message.from.language_code'),
                    'is_bot' => $this->request->getData('message.from.is_bot'),
                    'user_id' => $this->request->getData('message.from.id'),
                    'chat_id' => $this->request->getData('message.chat.id'),
                    'city_id' => 1510853, // barnaul
                ]);
                $this->Users->saveOrFail($user);
            }

            $owm = new OpenWeatherMap(Configure::read('OpenWeather.api_key'), GuzzleAdapter::createWithConfig([]) , new RequestFactory());
            $weather = $owm->getWeather($user->city_id, 'metric', $user->language_code);
            $temperature = $weather->temperature->now;

            $icons = [
                '01d' => '☀️',
                '01n' => '🌕',
                '02d' => '🌤',
                '02n' => '🌤',
                '03d' => '🌥',
                '03n' => '🌥',
                '04d' => '☁️',
                '04n' => '☁️',
                '09d' => '🌧',
                '09n' => '🌧',
                '10d' => '🌦',
                '10n' => '🌦',
                '11d' => '🌩',
                '11n' => '🌩',
                '13d' => '❄️',
                '13n' => '❄️',
                '50d' => '💨',
                '50n' => '💨',
            ];

            $bot->sendMessage($user->chat_id, $temperature->getValue() . $temperature->getUnit() . ' ' . $icons[$weather->weather->icon] . ' ' . $weather->wind->speed);

        } catch (\Throwable $e) {

            throw $e;
        }
    }

    protected function _start(BotApi $bot)
    {
        return $bot->sendMessage($this->request->getData('message.chat.id'), 'Hi, just send me your city');
    }
}
