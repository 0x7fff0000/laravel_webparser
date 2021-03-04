<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\Parsers\EscoService as EscoParser;
use Illuminate\Console\Command;

class ParseCategories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:categories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parse categories from websites';

    private EscoParser $m_escoParser;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->m_escoParser = new EscoParser;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $categories = $this->m_escoParser->parseCategories();

        foreach ($categories as &$category) {
            Category::updateOrCreate($category);
        }

        return 0;
    }
}
