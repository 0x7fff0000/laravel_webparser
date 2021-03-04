<?php

namespace App\Console\Commands;

use App\Models\Subcategory;
use App\Services\Parsers\EscoService as EscoParser;
use Illuminate\Console\Command;

class ParseProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse products from websites';

    private EscoParser $m_escoParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_escoParser = new EscoParser();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $subcategories = Subcategory::all();

        foreach ($subcategories as &$subcategory) {
            $products = $this->m_escoParser->parseSubcategoryPage($subcategory->href);

            foreach ($products as &$product) {
                $subcategory->products()->updateOrCreate($product);
            }
        }

        return 0;
    }
}
