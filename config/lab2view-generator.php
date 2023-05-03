<?php

return [

    'user_model_class' => 'App\\Models\\User',

    /*
    |--------------------------------------------------------------------------
    | Directories
    |--------------------------------------------------------------------------
    |
    | The default directory structure
    |
    */

    'models_directory' => app_path('Models/'),
    'contracts_directory' => app_path('Contracts/'),
    'repositories_directory' => app_path('Repositories/'),
    'policies_directory' => app_path('Policies/'),
    'resources_directory' => app_path('Http/Resources/'),

    /*
    |--------------------------------------------------------------------------
    | Namespaces
    |--------------------------------------------------------------------------
    |
    | The namespace of repository and models
    |
    */
    'models_namespace' => 'App\Models',
    'contracts_namespace' => 'App\Contracts',
    'repositories_namespace' => 'App\Repositories',
    'policies_namespace' => 'App\Policies',
    'resources_namespace' => 'App\Http\Resources',

    /*
    |--------------------------------------------------------------------------
    | Main Repository File
    |--------------------------------------------------------------------------
    |
    | The main repository class, other repositories will be extended from this
    |
    | If you're working with your customized repository file
    | You should change these values like below,
    |
    | 'base_repository_file' => 'CustomFile.php'
    | 'base_repository_class' => 'App\Custom\Repository:class'
    */

    // Only file name of the file because full path can cause errors.
    // We're going to use "repository_directory" config value for it.
    'base_repository_file' => 'BaseRepository.php',
    // Class name as string
    'base_repository_class' => \Lab2view\Generator\BaseRepository::class,

    // We're going to use "contracts_directory" config value for it.
    'base_contract_file' => 'RepositoryInterface.php',
    // Interface name as string
    'base_contract_interface' => \Lab2view\Generator\RepositoryInterface::class,

    // Only file name of the file because full path can cause errors.
    // We're going to use "policy_directory" config value for it.
    'base_policy_file' => 'BasePolicy.php',
    // Class name as string
    'base_policy_class' => \Lab2view\Generator\BasePolicy::class,
];
