<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class AutoLeechFanqienovel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leech:fanqienovel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Leech https://fanqienovel.com/';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    const URL = 'https://fanqienovel.com/page/';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (setting('is_leech_on_fanqie', false)) {
            $admin = User::where('id', 16)->first();
            $baseList = Http::get("https://fanqienovel.com/api/author/library/book_list/v0/?page_count=18&page_index=" . setting_custom('leech_fanqie_page', null,  0))
                ->json();
            if ($baseList['code'] == 0) {
                foreach ($baseList['data']['book_list'] as $datum) {
                    $url = self::URL . $datum['book_id'];
                    embedStoryUukanshu($url, '', $admin);
                }
                if (count($baseList['data']['book_list']) >= 5 ) {
                    setting_custom('leech_fanqie_page', setting_custom('leech_fanqie_page', null, 0) + 1);
                }
            }
        }
        return 0;
    }
}
