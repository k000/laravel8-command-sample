<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class InsertDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     * コマンド名を設定する
     *
     * @var string
     */
    protected $signature = 'command:insertdata {filename}';


    /**
     * The console command description.
     * コマンドの説明文を設定する
     *
     * @var string
     */
    protected $description = 'insert data from yaml file';

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
     * 実行する処理を記載する
     *
     * @return int
     */
    public function handle()
    {

        // コマンド引数で受取ったファイル名を取得する
        $filename = $this->argument('filename');

        // infoは緑色でコマンド出力されます
        $this->info("start Insert Data Command.");

        //引数のファイルがなければ処理を終わりにする。database_pathはヘルパ関数
        if( !file_exists(database_path('insertdata/'.$filename)) ){
            $this->error($filename . " not found");
            return Command::FAILURE;
        }

        // yamlファイルの読込
        try{
            $yaml = \Symfony\Component\Yaml\Yaml::parse(
                file_get_contents(database_path('insertdata/'.$filename))
            );
        } catch (Exception $e) {
            $this->error("fail read yaml file");
            return Command::FAILURE;
        }

        // データベースにインサートします
        try{
            DB::table($yaml['table'])->insert($yaml['data']);
        } catch (Exception $e){
            $this->error("fail insert records");
            return Command::FAILURE;
        }

        $this->info("Success Insert Data Command.");

        return Command::SUCCESS;
    }
}
