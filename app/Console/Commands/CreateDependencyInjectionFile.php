<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * インターフェースと実装クラスを作成します。
 * 引数は/で指定します。
 * 例: artisan command:createdifile App/Domain/Service/TestService.php
 * 
 * オプションによってProviderクラスも作成します。
 * オプションによってconfig/app.phpのProviderにProviderクラスの登録も行います。→多分無理
 */
class CreateDependencyInjectionFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:createdifile {filepath} {--p} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'create di file';

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
        // 引数で受取ったパスとファイル名
        $filepath =  $this->argument('filepath');

        // 区切り位置を取得する
        $separationPos = strrpos($filepath, "/");

        // フォルダのパス
        $folderPath = substr($filepath,0,$separationPos);

        // ファイル名
        $fileName = substr($filepath,$separationPos + 1);

        // フォルダがない場合はフォルダ事新規作成する必要があります。
        if (!file_exists($folderPath))
        {
            // folder/folder/folder/となっていた場合に第三引数でtrueを指定すると自動的に再帰的にフォルダ作成されます。
            mkdir($folderPath . "/Impl",0755,true);
        }


        $codePath = str_replace("/","\\",$folderPath);

        if(substr($codePath,0,3) === "app"){
            $codePath = substr_replace($codePath,"A",0,1);
        }

        $text = "<?php" . "\n" 
            . "namespace " . $codePath . ";" . "\n" 
            . "interface " . $fileName . " {" . "\n"
            . "}";
        
        $implText = "<?php" . "\n"
            . "namespace " . $codePath . "\\Impl" . ";" . "\n"
            . "use " . $codePath . "\\" . $fileName . ";" . "\n"
            . "class " . $fileName . "Impl " . "implements " . $fileName . " {" . "\n"
            . "}";
        
        // インターフェースの作成
        file_put_contents($folderPath . "/" . $fileName . ".php", $text, LOCK_EX);

        // 実装ファイルの作成
        file_put_contents($folderPath . "/Impl" . "/" . $fileName . "Impl.php", $implText, LOCK_EX);

        // オプションの有無によってはサービスプロバイダ作成コマンドを実行する
        if($this->option("p"))
        {
            Artisan::call("make:provider" . " " . $fileName . "ServiceProvider");
        }

        // 引数の内容によってはconfig.app.phpにサービスプロバイダを登録する
        // これ多分無理

        return Command::SUCCESS;
    }
}
