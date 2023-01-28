<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Botble\Tiki\Models\DiscountCode;

class checkHaveTikiSeller extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:check-have-tiki-seller';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check have tiki seller';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $discountCodes = DiscountCode::doesntHave('seller')->where('seller_id', '<>', 0)->select('id', 'seller_id')->get()->toArray();
        dump($discountCodes);
        return 0;
    }
}
