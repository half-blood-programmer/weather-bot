<?php
declare(strict_types=1);

namespace App\Command;

use App\Model\Entity\User;
use App\Utility\Weather;
use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\I18n\Time;
use Cmfcmf\OpenWeatherMap;
use TelegramBot\Api\BotApi;

/**
 * UpdateWeather command.
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UpdateWeatherCommand extends Command
{
    protected $modelClass = 'Users';
    protected BotApi $bot;
    protected Weather $owm;

    public function __construct()
    {
        parent::__construct();

        $this->owm = new Weather(Configure::read('OpenWeather.api_key'));
        $this->bot = new BotApi(Configure::read('Bot.api_key'));
        $this->bot->setProxy('socks5://v3_279932456:yYvsvPT1@s5.priv.opennetwork.cc:1080');
    }

    /**
     * Hook method for defining this command's option parser.
     *
     * @see https://book.cakephp.org/3.0/en/console-and-shells/commands.html#defining-arguments-and-options
     *
     * @param ConsoleOptionParser $parser The parser to be defined
     * @return ConsoleOptionParser The built parser.
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);

        return $parser;
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param Arguments $args The command arguments.
     * @param ConsoleIo $io The console io
     * @return null|void|int The exit code or null for success
     * @throws OpenWeatherMap\Exception
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $lastUpdatedWeather = Time::now()->timestamp;

        $users = $this->Users->find()->where([
            'available IS' => true,
            'city_id IS NOT' => null,
            'last_updated_weather <=' => Time::now()->subMinutes(30)->timestamp
        ]);

        /** @var User $user */
        foreach ($users as $user) {

            $forecast = $this->owm->getSimpleForecast($user->city_id, $user->language_code);

            $dailyForecastText = $this->owm->getDailyForecastMessage($forecast);
            $currentWeatherText = $this->owm->getCurrentWeatherMessage($forecast);

            try {
                $this->bot->editMessageText($user->chat_id, $user->daily_forecast_message_id, $dailyForecastText);
                $this->bot->editMessageText($user->chat_id, $user->current_weather_message_id, $currentWeatherText);
            }
            catch (\Exception $e) {
                /**
                 * Message or whole chat has been deleted
                 * Make user unavailable
                 */
                if ($e->getMessage() == 'Bad Request: message to edit not found') {
                  $user->available = false;
                  $this->Users->save($user);
                }

                continue;
            }

            $user->last_updated_weather = $lastUpdatedWeather;
            $this->Users->saveOrFail($user);
        }
    }
}
