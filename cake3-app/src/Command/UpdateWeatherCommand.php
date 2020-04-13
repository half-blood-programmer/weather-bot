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
        $owm = new Weather(Configure::read('OpenWeather.api_key'));
        $bot = new BotApi(Configure::read('Bot.api_key'));
        $bot->setProxy('socks5://v3_279932456:yYvsvPT1@s5.priv.opennetwork.cc:1080');

        $users = $this->Users->find()->where([
            'available IS' => true,
            'city_id IS NOT' => null,
            'last_updated_weather <' => Time::now()->subMinutes(30)->timestamp
        ]);

        /** @var User $user */
        foreach ($users as $user) {

            $message = $owm->getWeatherMessage($user->city_id, $user->language_code);

            try {
                $bot->editMessageText($user->chat_id, $user->weather_message_id, $message);
            }
            catch (\Exception $e) {
                continue;
            }

            $user->last_updated_weather = Time::now()->timestamp;
            $this->Users->saveOrFail($user);
        }
    }
}
