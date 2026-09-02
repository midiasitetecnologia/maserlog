<?php

// Se existir a variável de ambiente 'AWS_APP_ENV' indica que estamos rodando a APP na Amazon.
// Com isso, podemos setar em qual sistema de arquivos ('Filesystem') iremos armazenar os
// as imagens e/ou outros arquivos.
//
if(isset($_SERVER['AWS_APP_ENV'])) {

    define('MASER_FILESYSTEM_DRIVER', env('FILESYSTEM_CLOUD', 's3'));

    // No S3 definimos 'buckets' diferentes para os ambientes: 'production' e 'developer'.
    // Aqui adicionamos um sufixo ao 'bucket' do S3 conforme o ambiente.
    //
    // Exemplo de nome de bucket para ambiente produção: 'maser-prod'
    //    
    if( $_SERVER['AWS_APP_ENV'] == 'production') {
        $env_sufix = '-prod';
    } else {
        $env_sufix = '-dev';
    }

    define('MASER_S3_BUCKET', env('AWS_BUCKET', 'maser') . $env_sufix);

} else {

    define('MASER_FILESYSTEM_DRIVER', env('FILESYSTEM_DRIVER', 'local'));

    // Aqui atribuimos um valor para 'MASER_S3_BUCKET' só para não dar erro. 
    // Como aqui tratamos o armazenamento local, não precisamos do 'bucket' do S3.
    //
    define('MASER_S3_BUCKET', env('AWS_BUCKET', 'maser'));

}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => MASER_FILESYSTEM_DRIVER,

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported storage drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

        // Documento da Amazon que fala sobre o certificado para acessar S3
        //https://docs.aws.amazon.com/aws-sdk-php/v3/guide/faq.html#what-do-i-do-about-a-curl-ssl-certificate-error

        // Local de onde baixamos o certificado
        //https://curl.haxx.se/docs/caextract.html

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => MASER_S3_BUCKET,            
            'visibility' => 'public',                        
            'throw' => false,
            'report' => false,
        ],

    ],
           
    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
