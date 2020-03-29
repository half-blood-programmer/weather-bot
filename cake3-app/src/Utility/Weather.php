<?php
namespace App\Utility;

use Cake\I18n\Time;
use Cmfcmf\OpenWeatherMap;
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
     * @param $cityId
     * @param string $languageCode
     * @return string
     * @throws OpenWeatherMap\Exception
     */
    public function getWeatherMessage($cityId, $languageCode = 'en')
    {
        $weather = $this->getWeather($cityId, 'metric', $languageCode);

        return $this->buildMessage($weather);
    }

    /**
     * @param $cityId
     * @param string $languageCode
     * @return string
     * @throws OpenWeatherMap\Exception
     */
    public function getForecastMessage($cityId, $languageCode = 'en')
    {
        $forecast = $this->getWeatherForecast($cityId, 'metric', $languageCode, '', 5);
        $tzHours = (int) $forecast->city->timezone->getName();

        $timeToday = Time::now()->addHours($tzHours)->format('d.m');
        $todayPlus5days = Time::now()->addDays(5)->addHours($tzHours)->format('d.m');

        $i = 0;
        $message = '';

        foreach ($forecast as $weather) {

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
     * @param OpenWeatherMap\Util\Weather | OpenWeatherMap\CurrentWeather $weather
     * @return string
     */
    public function buildMessage($weather)
    {
        $speedInMetersPerSec = $weather->wind->speed->getValue();
        $speedInKmPerHour = (int) ($speedInMetersPerSec * 3600 / 1000);
        $wind = $speedInKmPerHour . ' km/h';

        $temperature = $weather->temperature->now;
        $temp = (int) $temperature->getValue() . $temperature->getUnit();

        return self::ICON[$weather->weather->icon] . ' ' . $temp . ' | ' . $wind;
    }
}
