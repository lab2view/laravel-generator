<?php

namespace Lab2view\Generator\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Lab2view\Generator\Exceptions\FileException;
use Lab2view\Generator\Exceptions\StubException;

class Generate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "lab2view:generator 
        {--c|contracts} 
        {--p|policies}
        {--r|resources}";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generating repositories, contracts, policies and resources from existing model files';

    /**
     * Overriding existing files.
     *
     * @var bool
     */
    protected bool $override = false;
    protected array $directories = [];
    protected array $namespaces = [];
    protected array $models = [];
    protected bool $hasContracts = false;
    protected bool $hasPolicies = false;
    protected bool $hasResources = false;

    public function __construct()
    {
        parent::__construct();

        $this->directories = [
            'contracts' => config('core-generator.contracts_directory'),
            'repositories' => config('core-generator.repositories_directory'),
            'policies' => config('core-generator.policies_directory'),
            'resources' => config('core-generator.resources_directory'),
            'models' => config('core-generator.models_directory')
        ];

        $this->namespaces = [
            'contracts' => config('core-generator.contracts_namespace'),
            'repositories' => config('core-generator.repositories_namespace'),
            'policies' => config('core-generator.policies_namespace'),
            'resources' => config('core-generator.resources_namespace'),
            'models' => config('core-generator.models_namespace')
        ];
    }

    /**
     * Execute the console command.
     *
     * @return void
     * @throws FileException|StubException
     */
    public function handle(): void
    {
        // Check repositories' folder permissions.
        $this->checkRepositoriesPermissions();

        // Get all model file names.
        $this->models = $this->getModels();

        // Check model files.
        if (count($this->models) === 0) {
            $this->noModelsMessage();
        }

        if ($this->hasContracts = $this->option('contracts')) {
            // Check contracts folder permissions.
            $this->checkContractsPermissions();

            $this->createContracts();
        }

        if ($this->hasPolicies = $this->option('policies')) {
            // Check if policies are required.
            $this->checkPoliciesPermissions();

            $this->createPolicies();
        }

        if ($this->hasResources = $this->option('resources')) {
            // Check if policies are required.
            $this->checkResourcesPermissions();

            $this->createResources();
        }

        $this->createRepositories();
    }

    /**
     * Get all model names from models directory.
     *
     * @return array
     */
    private function getModels(): array
    {
        if (!is_dir($this->directories['models'])) {
            $this->error('The models directory does not exist.');
            exit;
        }

        $models = glob($this->directories['models'] . '*.php');
        return str_replace([$this->directories['models'], '.php'], '', $models);
    }

    /**
     * Get stub content.
     *
     * @param $file
     * @return bool|string
     * @throws StubException
     */
    private function getStub($file): bool|string
    {
        $stub = __DIR__ . '/../Stubs/' . $file . '.stub';
        if (file_exists($stub)) {
            return file_get_contents($stub);
        }
        throw StubException::fileNotFound($file);
    }

    /**
     * Get contracts path.
     *
     * @param null|string $path
     * @return string
     */
    private function contractsPath(string $path = null): string
    {
        return $this->directories['contracts'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get policies path.
     *
     * @param null|string $path
     * @return string
     */
    private function policiesPath(string $path = null): string
    {
        return $this->directories['policies'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get resources path.
     *
     * @param null|string $path
     * @return string
     */
    private function resourcesPath(string $path = null): string
    {
        return $this->directories['resources'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Get repositories path.
     *
     * @param null|string $path
     * @return string
     */
    private function repositoriesPath(string $path = null): string
    {
        return $this->directories['repositories'] . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * @param string $class
     * @return string|null
     */
    public function fileNamespace(string $class): ?string
    {
        if (in_array(mb_strtolower(substr($class, 0, 4)), ['app\\', 'app/'])) {
            return app_path(substr($class, 4));
        }

        if (class_exists($class)) {
            return base_path($class);
        }

        return null;
    }

    /**
     * Get parent path of repository of interface folder.
     *
     * @param string $child
     * @return string
     */
    private function parentPath(string $child): string
    {
        if (!is_dir($child)) {
            mkdir($child, 0777, true);
        }

        return dirname($child);
    }

    /**
     * Generate/override a file.
     *
     * @param $file
     * @param $content
     */
    private function writeFile($file, $content)
    {
        file_put_contents($file, $content);
    }

    /**
     * Check repositories' folder permissions.
     *
     * @throws FileException
     */
    private function checkRepositoriesPermissions()
    {
        // Get full path of repository directory.
        $repositoriesPath = $this->repositoriesPath();

        // Get parent directory of repository path.
        $repositoryParentPath = $this->parentPath($repositoriesPath);

        // Check parent of repository directory is writable.
        if (!file_exists($repositoriesPath) && !is_writable($repositoryParentPath)) {
            throw FileException::notWritableDirectory($repositoryParentPath);
        }

        // Check repository directory permissions.
        if (file_exists($repositoriesPath) && !is_writable($repositoriesPath)) {
            throw FileException::notWritableDirectory($repositoriesPath);
        }
    }

    /**
     * Check repository folder permissions.
     *
     * @throws FileException
     */
    private function checkContractsPermissions()
    {
        // Get full path of contracts directory.
        $contractsPath = $this->contractsPath();

        // Get parent directory of contracts path.
        $contractsParentPath = $this->parentPath($contractsPath);

        // Check parent of contracts directory is writable.
        if (!file_exists($contractsPath) && !is_writable($contractsParentPath)) {
            throw FileException::notWritableDirectory($contractsParentPath);
        }

        // Check contracts directory permissions.
        if (file_exists($contractsPath) && !is_writable($contractsPath)) {
            throw FileException::notWritableDirectory($contractsPath);
        }
    }

    /**
     * @throws FileException
     */
    private function checkPoliciesPermissions()
    {
        // Get full path of policies directory.
        $policiesPath = $this->policiesPath();

        // Get parent directory of policies path.
        $policiesParentPath = $this->parentPath($policiesPath);

        // Check parent of policies directory is writable.
        if (!file_exists($policiesPath) && !is_writable($policiesParentPath)) {
            throw FileException::notWritableDirectory($policiesParentPath);
        }

        // Check policies' directory permissions.
        if (file_exists($policiesPath) && !is_writable($policiesPath)) {
            throw FileException::notWritableDirectory($policiesPath);
        }
    }



    /**
     * @throws FileException
     */
    private function checkResourcesPermissions()
    {
        // Get full path of policies directory.
        $resourcesPath = $this->resourcesPath();

        // Get parent directory of resources path.
        $resourcesParentPath = $this->parentPath($resourcesPath);

        // Check parent of resources directory is writable.
        if (!file_exists($resourcesPath) && !is_writable($resourcesParentPath)) {
            throw FileException::notWritableDirectory($resourcesParentPath);
        }

        // Check resources' directory permissions.
        if (file_exists($resourcesPath) && !is_writable($resourcesPath)) {
            throw FileException::notWritableDirectory($resourcesPath);
        }
    }

    /**
     * @param string $folder
     * @return void
     */
    private function createFolder(string $folder): void
    {
        if (!file_exists($folder)) {
            mkdir($folder);
        }
    }

    /**
     * Show message and stop script, If there are no model files to work.
     */
    private function noModelsMessage()
    {
        $this->warn('Repository generator has stopped!');
        $this->line(
            'There are no model files to use in directory: "'
            . config('core-generator.models_directory')
            . '"'
        );
        return;
    }

    /**
     * @throws StubException
     */
    protected function createPolicies()
    {
        // Create policies folder if it's necessary.
        $this->createFolder($this->directories['policies']);

        // Get existing policy file names.
        $existingPolicyFiles = glob($this->policiesPath('*.php'));

        // Remove main policy file name from array
        $existingPolicyFiles = array_diff(
            $existingPolicyFiles,
            [$this->policiesPath(config('core-generator.base_policy_file'))]
        );

        // Ask for overriding, If there are files in policies directory.
        if (count($existingPolicyFiles) > 0 && !$this->override) {
            if ($this->confirm('Do you want to overwrite the existing policies ? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $policyStub = $this->getStub('Policy');

        // Policy stub values those should be changed by command.
        $policyStubValues = [
            '{{ use_statement_for_user_model }}',
            '{{ policies_namespace }}',
            '{{ policy }}',
            '{{ models_namespace }}',
            '{{ model }}',
            '{{ modelVariable }}',
            '{{ base_policy }}'
        ];

        foreach ($this->models as $model) {
            $policy = $model . 'Policy';

            // Current policy file name
            $policyFile = $this->policiesPath($policy . '.php');

            // Check main policy file's path to add use
            if (dirname($policyFile) !== dirname(config('core-generator.base_policy_file'))) {
                $mainPolicy = config('core-generator.base_policy_class');
            }

            // User Model
            $userClass = config('core-generator.user_model_class');
            $useStatementForUserModel = false;

            if (class_exists($userClass)) {
                $useStatementForUserModel = 'use ' . $userClass . ';';
            }

            // Fillable policy values for generating real files
            $policyValues = [
                $useStatementForUserModel ?: '',
                $this->namespaces['policies'],
                $policy,
                $this->namespaces['models'],
                $model,
                mb_strtolower($model),
                str_replace('.php', '', config('core-generator.base_policy_file')),
            ];

            // Generate body of the policy file
            $policyContent = str_replace(
                $policyStubValues,
                $policyValues,
                $policyStub
            );

            $basePolicyPath = $this->directories['policies'] . config('core-generator.base_policy_file');
            if (Storage::fileExists($basePolicyPath)) {
                Storage::copy(base_path('BasePolicy'), $basePolicyPath);
            }

            if (in_array($policyFile, $existingPolicyFiles)) {
                if ($this->override) {
                    $this->writeFile($policyFile, $policyContent);
                    $this->info('Overridden policy file: ' . $policy);
                }
            } else {
                $this->writeFile($policyFile, $policyContent);
                $this->info('Created policy file: ' . $policy);
            }

            $this->override = false;
        }
    }

    /**
     * @throws StubException
     */
    protected function createResources()
    {
        // Create resources folder if it's necessary.
        $this->createFolder($this->directories['resources']);

        // Get existing resource file names.
        $existingResourceFiles = glob($this->resourcesPath('*.php'));

        // Ask for overriding, If there are files in resources directory.
        if (count($existingResourceFiles) > 0 && !$this->override) {
            if ($this->confirm('Do you want to overwrite the existing resources ? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $resourceStub = $this->getStub('Resource');

        // Policy stub values those should be changed by command.
        $resourceStubValues = [
            '{{ namespace }}',
            '{{ class }}'
        ];

        foreach ($this->models as $model) {
            $resource = $model . 'Resource';

            // Current resource file name
            $resourceFile = $this->resourcesPath($resource . '.php');

            // Fillable resource values for generating real files
            $resourceValues = [
                $this->namespaces['resources'],
                $resource
            ];

            // Generate body of the policy file
            $resourceContent = str_replace(
                $resourceStubValues,
                $resourceValues,
                $resourceStub
            );

            if (in_array($resourceFile, $existingResourceFiles)) {
                if ($this->override) {
                    $this->writeFile($resourceFile, $resourceContent);
                    $this->info('Overridden resource file: ' . $resource);
                }
            } else {
                $this->writeFile($resourceFile, $resourceContent);
                $this->info('Created resource file: ' . $resource);
            }

            $this->override = false;
        }
    }

    /**
     * @throws StubException
     */
    protected function createRepositories()
    {
        // Create repositories folder if it's necessary.
        $this->createFolder($this->directories['repositories']);

        // Get existing repository file names.
        $existingRepositoryFiles = glob($this->repositoriesPath('*.php'));

        // Remove main repository file name from array
        $existingRepositoryFiles = array_diff(
            $existingRepositoryFiles,
            [$this->repositoriesPath(config('core-generator.base_repository_file'))]
        );

        // Ask for overriding, If there are files in repositories directory.
        if (count($existingRepositoryFiles) > 0 && !$this->override) {
            if ($this->confirm('Do you want to overwrite the existing repositories ? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $repositoryStub = $this->getStub($this->hasContracts ? 'RepositoryEloquent' : 'Repository');

        // Repository stub values those should be changed by command.
        $repositoryStubValues = [
            '{{ use_statement_for_repository }}',
            '{{ repositories_namespace }}',
            '{{ base_repository }}',
            '{{ repository }}',
            '{{ models_namespace }}',
            '{{ model }}'
        ];

        if ($this->hasContracts) {
            $repositoryStubValues[] = '{{ use_statement_for_contract }}';
        }

        foreach ($this->models as $model) {
            $repository = $model . ($this->hasContracts ? 'RepositoryEloquent' : 'Repository');

            // Current repository file name
            $repositoryFile = $this->repositoriesPath($repository . '.php');

            // Check main repository file's path to add use
            $useStatementForRepository = false;
            if (dirname($repositoryFile) !== dirname(config('core-generator.base_repository_file'))) {
                $mainRepository = config('core-generator.base_repository_class');
                $useStatementForRepository = 'use ' . $mainRepository . ';';
            }

            // Check main repository file's path to add use
            $useStatementForContract = false;
            if ($this->hasContracts) {
                // Current repository file name
                $contractFile = $this->contractsPath($model . 'Repository.php');

                if (is_file($contractFile)) {
                    $mainContract = $this->namespaces['contracts'];
                    $useStatementForContract = 'use ' . $mainContract . '\\' . $model . 'Repository;';
                }
            }

            // Fillable repository values for generating real files
            $repositoryValues = [
                $useStatementForRepository ?: '',
                $this->namespaces['repositories'],
                str_replace('.php', '', config('core-generator.base_repository_file')),
                $repository,
                $this->namespaces['models'],
                $model
            ];

            if ($this->hasContracts) {
                $repositoryValues[] = $useStatementForContract ?: '';
            }

            // Generate body of the repository file
            $repositoryContent = str_replace(
                $repositoryStubValues,
                $repositoryValues,
                $repositoryStub
            );

            if (in_array($repositoryFile, $existingRepositoryFiles)) {
                if ($this->override) {
                    $this->writeFile($repositoryFile, $repositoryContent);
                    $this->info('Overridden repository file: ' . $repository);
                }
            } else {
                $this->writeFile($repositoryFile, $repositoryContent);
                $this->info('Created repository file: ' . $repository);
            }

            $this->override = false;
        }
    }

    /**
     * @param string $namespace
     * @return string
     */
    public function generateNamespace(string $namespace): string
    {
        return ucwords(str_replace('/', '\\', $namespace), '\\');
    }

    /**
     * @throws StubException
     */
    protected function createContracts()
    {
        // Create contracts folder if it's necessary.
        $this->createFolder($this->namespaces['contracts']);

        // Get existing contract file names.
        $existingContractFiles = glob($this->contractsPath('*.php'));

        // Remove main contract file name from array
        $existingContractFiles = array_diff(
            $existingContractFiles,
            [$this->contractsPath(config('core-generator.base_contract_file'))]
        );

        // Ask for overriding, If there are files in contracts directory.
        if (count($existingContractFiles) > 0 && !$this->override) {
            if ($this->confirm('Do you want to overwrite the existing contracts ? (Yes/No):')) {
                $this->override = true;
            }
        }

        // Get stub file templates.
        $contractStub = $this->getStub('Contract');

        // Contract stub values those should be changed by command.
        $contractStubValues = [
            '{{ use_statement_for_contract }}',
            '{{ contracts_namespace }}',
            '{{ base_contract }}',
            '{{ contract }}'
        ];

        foreach ($this->models as $model) {
            $contract = $model . 'Repository';

            // Current contract file name
            $contractFile = $this->contractsPath($contract . '.php');

            // Check main contract file's path to add use
            $useStatementForContract = false;
            if (dirname($contractFile) !== dirname(config('core-generator.base_contract_file'))) {
                $mainContract = config('core-generator.base_contract_interface');
                $useStatementForContract = 'use ' . $mainContract . ';';
            }

            // Fillable contract values for generating real files
            $contractValues = [
                $useStatementForContract ?: '',
                $this->generateNamespace($this->namespaces['contracts']),
                str_replace('.php', '', config('core-generator.base_contract_file')),
                $contract
            ];

            // Generate body of the contract file
            $contractContent = str_replace(
                $contractStubValues,
                $contractValues,
                $contractStub
            );

            if (in_array($contractFile, $existingContractFiles)) {
                if ($this->override) {
                    $this->writeFile($contractFile, $contractContent);
                    $this->info('Overridden contract file: ' . $contract);
                }
            } else {
                $this->writeFile($contractFile, $contractContent);
                $this->info('Created contract file: ' . $contract);
            }

            $this->override = false;
        }
    }
}
