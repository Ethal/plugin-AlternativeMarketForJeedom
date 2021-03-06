<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


class MarketItem
{
    /**
     * @var int Temps de rafraichissement d'un dépôt
     */
    private $REFRESH_TIME_LIMIT = 86400;

    /**
     * @var string Identifiant du plugin
     */
    private $id;
    /**
     * @var string Nom du plugin sur GitHub
     */
    private $gitName;
    /**
     * @var string Utilisateur GitHub
     */
    private $gitUser;
    /**
     * @var string Nom complet de son dépôt
     */
    private $fullName;
    /**
     * @var string Description
     */
    private $description;
    /**
     * @var string URL Git
     */
    private $url;
    /**
     * @var string Nom du plug
     */
    private $name;
    /**
     * @var string Auteur du plugin
     */
    private $author;
    /**
     * @var string Catégorie du plugin
     */
    private $category;
    /**
     * @var DataStorage Gestionnaire de base de données
     */
    private $dataStorage;
    /**
     * @var string Chemin de l'icône
     */
    private $iconPath;
    /**
     * @var string Branche par défaut
     */
    private $defaultBranch;
    /**
     * @var array Liste des branches
     */
    private $branchesList;
    /**
     * @var string Licence
     */
    private $licence;
    /**
     * @var string Lien vers la documentation
     */
    private $documentationLink;
    /**
     * @var string Lien vers le changelog
     */
    private $changelogLink;

    /**
     * Constructeur initialisant les informations de base
     *
     * @param $repositoryInformations Informations obtenus par GitHub
     */
    public function __construct($repositoryInformations)
    {
        $this->initWithGlobalInformations($repositoryInformations);
        $this->dataStorage = new AmfjDataStorage('amfj');
    }

    /**
     * Lire les informations obtenus par GitHub
     *
     * @param $repositoryInformations Informations de GitHub
     */
    public function initWithGlobalInformations($repositoryInformations)
    {
        $this->gitName = $repositoryInformations['name'];
        $this->fullName = $repositoryInformations['full_name'];
        $this->url = $repositoryInformations['html_url'];
        $this->gitUser = $repositoryInformations['git_user'];
        $this->description = $repositoryInformations['description'];
        $this->defaultBranch = $repositoryInformations['default_branch'];
    }

    /**
     * Ajouter les informations contenu dans le fichier info.json du plugin
     *
     * @param array $pluginInfo Contenu du fichier info.json
     */
    public function addPluginInformations($pluginInfo)
    {
        if (\array_key_exists('id', $pluginInfo)) $this->id = $pluginInfo['id'];
        if (\array_key_exists('name', $pluginInfo)) $this->name = $pluginInfo['name'];
        if (\array_key_exists('author', $pluginInfo)) $this->author = $pluginInfo['author'];
        if (\array_key_exists('category', $pluginInfo)) $this->category = $pluginInfo['category'];
        if (\array_key_exists('licence', $pluginInfo)) $this->licence = $pluginInfo['licence'];
        if (\array_key_exists('changelog', $pluginInfo)) $this->changelogLink = $pluginInfo['changelog'];
        if (\array_key_exists('documentation', $pluginInfo)) $this->documentationLink = $pluginInfo['documentation'];
        if (\array_key_exists('description', $pluginInfo) && $pluginInfo['description'] !== null && $pluginInfo['description'] !== '') {
            $this->description = $pluginInfo['description'];
        }
    }

    /**
     * Test si une mise à jour est nécessaire
     *
     * @param $repositoryInformations Informations de GitHub
     *
     * @return bool True si une mise à jour est nécessaire
     */
    public function isNeedUpdate($repositoryInformations)
    {
        $result = true;
        $lastUpdate = $this->dataStorage->getRawData('repo_last_update_' . \str_replace('/', '_', $repositoryInformations['full_name']));
        if ($lastUpdate !== null) {
            if (\time() - $lastUpdate < $this->REFRESH_TIME_LIMIT) {
                return false;
            }
        }
        return $result;
    }

    /**
     * Obtenir l'ensemble des informations dans un tableau associatif
     *
     * @return array Tableau des données
     */
    public function getDataInArray()
    {
        $dataArray = array();
        $dataArray['name'] = $this->name;
        $dataArray['gitName'] = $this->gitName;
        $dataArray['gitUser'] = $this->gitUser;
        $dataArray['fullName'] = $this->fullName;
        $dataArray['description'] = $this->description;
        $dataArray['url'] = $this->url;
        $dataArray['id'] = $this->id;
        $dataArray['author'] = $this->author;
        $dataArray['category'] = $this->category;
        $dataArray['installed'] = $this->isInstalled();
        $dataArray['iconPath'] = $this->iconPath;
        $dataArray['defaultBranch'] = $this->defaultBranch;
        $dataArray['branchesList'] = $this->branchesList;
        $dataArray['licence'] = $this->licence;
        $dataArray['changelogLink'] = $this->changelogLink;
        $dataArray['documentationLink'] = $this->documentationLink;
        return $dataArray;
    }

    /**
     * Ecrire le fichier de cache au format JSON
     */
    public function writeCache()
    {
        $dataArray = $this->getDataInArray();
        $this->dataStorage->storeJsonData('repo_data_' . str_replace('/', '_', $this->fullName), $dataArray);
        $this->dataStorage->storeRawData('repo_last_update_' . str_replace('/', '_', $this->fullName), \time());
    }

    /**
     * Lire le fichier de cache
     *
     * @return bool True si la lecture a réussi
     */
    public function readCache()
    {
        $result = false;
        $jsonContent = $this->dataStorage->getJsonData('repo_data_' . str_replace('/', '_', $this->fullName));
        if ($jsonContent !== null) {
            if (\array_key_exists('name', $jsonContent)) $this->name = $jsonContent['name'];
            if (\array_key_exists('gitName', $jsonContent)) $this->gitName = $jsonContent['gitName'];
            if (\array_key_exists('gitUser', $jsonContent)) $this->gitUser = $jsonContent['gitUser'];
            if (\array_key_exists('fullName', $jsonContent)) $this->fullName = $jsonContent['fullName'];
            if (\array_key_exists('description', $jsonContent)) $this->description = $jsonContent['description'];
            if (\array_key_exists('url', $jsonContent)) $this->url = $jsonContent['url'];
            if (\array_key_exists('id', $jsonContent)) $this->id = $jsonContent['id'];
            if (\array_key_exists('author', $jsonContent)) $this->author = $jsonContent['author'];
            if (\array_key_exists('category', $jsonContent)) $this->category = $jsonContent['category'];
            if (\array_key_exists('iconPath', $jsonContent)) $this->iconPath = $jsonContent['iconPath'];
            if (\array_key_exists('defaultBranch', $jsonContent)) $this->defaultBranch = $jsonContent['defaultBranch'];
            if (\array_key_exists('branchesList', $jsonContent)) $this->branchesList = $jsonContent['branchesList'];
            if (\array_key_exists('licence', $jsonContent)) $this->licence = $jsonContent['licence'];
            if (\array_key_exists('changelogLink', $jsonContent)) $this->changelogLink = $jsonContent['changelogLink'];
            if (\array_key_exists('documentationLink', $jsonContent)) $this->documentationLink = $jsonContent['documentationLink'];
            $result = true;
        }
        return $result;
    }

    /**
     * Met à jour les données de l'élement
     *
     * @param AmfjDownloadManager $downloadManager Gestionnaire de téléchargement
     *
     * @return bool True si la mise à jour a été effectuée.
     */
    public function refresh($downloadManager)
    {
        $result = false;
        $infoJsonUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/'.$this->defaultBranch.'/plugin_info/info.json';
        $infoJson = $downloadManager->downloadContent($infoJsonUrl);
        if (strpos($infoJson, '404: Not Found') === false) {
            $pluginData = \json_decode($infoJson, true);
            $this->addPluginInformations($pluginData);
            $this->downloadIcon($downloadManager);
            $this->downloadGitInformations($downloadManager);
            $this->writeCache();
            $result = true;
        }
        return $result;
    }

    /**
     * Télécharge l'icône du plugin
     *
     * @param AmfjDownloadManager $downloadManager Gestionnaire de téléchargement
     */
    public function downloadIcon($downloadManager) {
        $iconFilename = \str_replace('/', '_', $this->fullName) . '.png';
        $iconUrl = 'https://raw.githubusercontent.com/' . $this->fullName . '/'.$this->defaultBranch.'/plugin_info/' . $this->id . '_icon.png';
        $targetPath = dirname(__FILE__) . '/../../cache/' . $iconFilename;
        $downloadManager->downloadBinary($iconUrl, $targetPath);
        if (\filesize($targetPath) < 100) {
            unlink($targetPath);
            $this->iconPath = 'core/img/no-image-plugin.png';
        } else {
            $this->iconPath = 'plugins/AlternativeMarketForJeedom/cache/' . $iconFilename;
        }

    }

    /**
     * Met à jour les données de Git
     *
     * @param AmfjDownloadManager $downloadManager Gestionnaire de téléchargement
     *
     * @return bool True si les données ont été trouvées
     */
    public function downloadGitInformations($downloadManager) {
        $baseGitRepoUrl = 'https://api.github.com/repos/'.$this->fullName;
        $branches = $downloadManager->downloadContent($baseGitRepoUrl.'/branches');
        if ($branches !== false) {
            $branches = \json_decode($branches, true);
            $this->branchesList = array();
            foreach ($branches as $branch) {
                array_push($this->branchesList, $branch['name']);
            }

        }
    }

    /**
     * Test si le plugin est installée
     *
     * @return bool True si le plugin est installée
     */
    public function isInstalled()
    {
        $result = false;
        if (\file_exists(\dirname(__FILE__) . '/../../../' . $this->id)) {
            $result = true;
        }
        return $result;
    }

    /**
     * Obtenir le nom du dépot.
     *
     * @return string Nom du dépot
     */
    public function getGitName()
    {
        return $this->gitName;
    }

    /**
     * Obtenir le nom complet
     *
     * @return string Nom complet
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Obtenir la description du dépot
     *
     * @return string Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Obtenir le lien
     *
     * @return string Lien
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Obtenir l'identifiant
     *
     * @return string Identifiant
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Obtenir l'auteur
     *
     * @return string Auteur
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Obtenir la catégorie
     *
     * @return string Catégorie
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Obtenir le nom
     *
     * @return string Nom
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Obtenir l'utilisateur GitHub
     *
     * @return string Utilisateur GitHub
     */
    public function getGitUser()
    {
        return $this->gitUser;
    }
}