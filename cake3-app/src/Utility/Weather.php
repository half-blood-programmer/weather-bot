<?php
namespace App\Utility;

use Cake\Cache\Cache;
use Cake\I18n\Time;
use Cake\Log\Log;
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\WeatherForecast;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Laminas\Diactoros\RequestFactory;

class Weather extends OpenWeatherMap
{
    const ICON = [
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

    /**
     * Weather constructor.
     *
     * @param $apiKey
     * @param null $cache
     * @param int $ttl
     */
    public function __construct($apiKey, $cache = null, $ttl = 600)
    {
        parent::__construct($apiKey, GuzzleAdapter::createWithConfig([]), new RequestFactory(), $cache, $ttl);
    }

    /**
     * @param WeatherForecast $forecast
     * @return string
     */
    public function getCurrentWeatherMessage(WeatherForecast $forecast)
    {

        $tzHours = (int) $forecast->city->timezone->getName();

        $timeToday = Time::now()->addHours($tzHours)->format('d.m');

        $i = 0;
        $message = '';

        foreach ($forecast as $weather) {

            if ($i == 5) {
                break;
            }

            if ($i == 0) {
                $now = (int) $weather->temperature->now->getValue();
            }

            Log::debug(json_encode($weather));

            $time = Time::createFromTimestamp($weather->time->from->getTimestamp())
                ->addHours($tzHours)
                ->format('d.m');

            if ($time == $timeToday || $time == $todayPlus5days) {
                continue;
            }

            $i++;
        }

        Log::debug(json_encode($weather->current()));
        $speedInMetersPerSec = $weather->wind->speed->getValue();
        $speedInKmPerHour = (int) ($speedInMetersPerSec * 3600 / 1000);
        $wind = $speedInKmPerHour . ' km/h';

        $min = (int) $weather->temperature->min->getValue();
        $max = (int) $weather->temperature->max->getValue();

        $now = $now > 0 ? '+' . $now : $now;
        $min = $min > 0 ? '+' . $min : $min;
        $max = $max > 0 ? '+' . $max : $max;

        return self::ICON[$weather->weather->icon] . " {$now}°    {$min}°/{$max}°        $wind";
    }

    /**
     * @param WeatherForecast $forecast
     * @return string
     * @throws OpenWeatherMap\Exception
     */
    public function getDailyForecastMessage(WeatherForecast $forecast)
    {
        $tzHours = (int) $forecast->city->timezone->getName();

        $timeToday = Time::now()->addHours($tzHours)->format('d.m');
        $todayPlus5days = Time::now()->addDays(5)->addHours($tzHours)->format('d.m');

        Log::debug(json_encode($forecast));

        $i = 0;
        $message = '';

        foreach ($forecast as $weather) {
            Log::debug(json_encode($weather));

            $time = Time::createFromTimestamp($weather->time->from->getTimestamp())
                ->addHours($tzHours)
                ->format('d.m');

            if ($time == $timeToday || $time == $todayPlus5days) {
                continue;
            }

            $i++;

            if ($i == 5) {
                $message .= $time . ' ' .  $this->buildMessage($weather) . PHP_EOL;
            }

            if ($i == 8) {
                $i = 0;
            }
        }

        return $message;
    }

    /**
     * @param OpenWeatherMap\WeatherForecast $weather
     * @return string
     */
    public function getWeatherUpdatedMessage(WeatherForecast $weather)
    {
        $timeUpdated = Time::now()
            ->addHours((int) $weather->city->timezone->getName())
            ->format('d.m H:i');

        return "Weather in {$weather->city->name} updated: $timeUpdated";
    }

    /**
     * Get forecast weather data, cached if called less that hour ago
     *
     * @param $cityId
     * @param string $languageCode
     * @return OpenWeatherMap\WeatherForecast|mixed
     * @throws OpenWeatherMap\Exception
     */
    public function _getForecast($cityId, $languageCode = 'en')
    {
        $result = Cache::read($cityId, 'weatherData');

        if (!$result) {
            $result = $this->getWeatherForecast($cityId, 'metric', $languageCode, '', 5);
            Cache::write($cityId, $result, 'weatherData');
        }

        return $result;
    }
}
