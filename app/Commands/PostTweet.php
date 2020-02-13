<?php

namespace App\Commands;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class PostTweet extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'post:tweet';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'This commands posts new tweet on Twitter Timeline';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $weather_tweet = $this->getTweetText();

        $this->postTweetOnTwitter($weather_tweet);
    }


    /**
     * This function gets current weather info and returns string 
     * 
     * @return string
     */
    public function getTweetText(){
        $client = new \GuzzleHttp\Client();
        $request = $client->get('https://api.openweathermap.org/data/2.5/weather?id=1262180&units=metric&appid=6c743793a5539486877ce02335831dc3');
        $response = $request->getBody();

        //Weather Data
        $data = json_decode($response);


        //Tweet String
        $weather_tweet = "Current Temperature ".$data->main->temp."°С , \nwind ".$data->wind->speed." m/s. clouds ".$data->clouds->all." %,\nat ".Carbon::now('Asia/Kolkata')->toDateTimeString()." \n#Nagpur #NagpurWeather ";

        return $weather_tweet;
    }

    /** 
     * Post tweet on twitter using Dusk
     * 
     */
    public function postTweetOnTwitter($tweet){
        $this->browse(function ($browser) use($tweet)  {

            $browser->visit('https://twitter.com/login')
                    ->pause(4000);


            $browser->type('session[username_or_email]', env('twitter_username'))
                    ->type('session[password]', env('twitter_password'))
                    ->pause(1000)
                    ->click('div[role="button"]')
                    ->pause(4000);

                if($browser->element('#challenge_response') != null){
                        $browser->value('#challenge_response', env('challenge_response'))
                                ->click('#email_challenge_submit')
                                ->pause(3000);
                }

                //Post Tweet
                $browser->assertSee('Home')
                    ->pause(2000)
                    ->click('.public-DraftStyleDefault-block')
                    ->keys(".public-DraftStyleDefault-block", $tweet)
                    ->pause(4000)
                    ->click('div[data-testid="tweetButtonInline"]')
                    ->pause(3000);
         
        });
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        $schedule->command(static::class)->everyThirtyMinutes();
    }
}
