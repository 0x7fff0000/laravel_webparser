<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Subcategory;
use App\Services\Parsers\EscoService as EscoParser;
use Illuminate\Console\Command;

class ParseSubcategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:subcategories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse subcategories from websites';

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
        $categories = Category::all();

        foreach ($categories as &$category) {
            $subcategories = $this->m_escoParser->parseCategoryPage($category->href);

            foreach ($subcategories as &$subcategory) {
                $category->subcategory()->updateOrCreate($subcategory);
            }
        }
        /*
        $subcategories = $this->m_escoParser->getSubcategories();

        foreach ($subcategories as &$subcategory) {
            Subcategory::updateOrCreate($sub)
        }
        */
        return 0;
    }
}
