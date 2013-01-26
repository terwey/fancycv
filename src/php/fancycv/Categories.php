<?php
namespace fancycv;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Categories
{
    private $_log;
	private $_skills;
	private $_skillsFile;
    public function __construct() {
        $this->_log = new Logger('Categories');
        $this->_log->pushHandler(new StreamHandler(LOG_DIR.'categories.log'), 
                                 Logger::WARNING);
    	$this->_skillsFile = DATA_DIR.'skills.yml';
        if (file_exists($this->_skillsFile)) {
            $this->_log->addDebug('Skills file exists. Parsing.', 
                                  array('filename' => $this->_skillsFile));
            $this->_skills = Yaml::parse($this->_skillsFile);
            $this->_log->addDebug('Skills file parsed.', 
                                  array('filename' => $this->_skillsFile,
                                        'parsedContents'=> $this->_skills));
        } else {
            $this->_log->addDebug('Skills file did not exist, creating empty array.');
            $this->_skills = array();
        }
    }

    public function save() {
        $status = Helpers::createFile(Yaml::dump($this->_skills, 3), // 2nd option in the Yaml::dump
                                      'skills.yml',                  // defines the inline switch, 
                                      $this->_skillsFile);           // 3 keeps it tidy
        if (!$status) {
            $this->_log->addError('Did not save.', 
                                  array('filename'=>$this->_skillsFile));
        } else {
            $this->_log->addDebug('Saved.', 
                                  array('filename'=>$this->_skillsFile));
        }
        return $status;
    }

    public function newCategory($categoryName, $categoryDesc=NULL) {
        print 'existing categories: ';
        var_dump($this->listCategories());
    	if (!in_array($categoryName, $this->listCategories())) {
    		$this->_skills[$categoryName] = array('desc' => $categoryDesc, 
                                                  'skills' => array());
    		if ($this->save()) {
                $this->_log->addDebug('Category ('.$categoryName.') did not yet exist. Created.');
                return true;
            } else {
                $this->_log->addError('Category ('.$categoryName.') did not yet exist. Failed to save');
                return false;
            }
    	} else {
            $this->_log->addWarning('Category already exists: '.$categoryName);
            return true;
        }
    }

    public function listCategories() {
    	return array_keys($this->_skills);
    }

    public function listSkillsInCategory($categoryName) {
        return array_keys($this->_skills[$categoryName]['skills']);
    }

    public function addSkillToCategory($skill, $categoryName, $skillDesc=NULL) {
        if (!in_array($skill, $this->listSkillsInCategory($categoryName))) {
            $this->_skills[$categoryName]['skills'][$skill] = $skillDesc;
            if ($this->save()) {
                $this->_log->addDebug('Skill ('.$skill.') did not yet exist in Category ('.$categoryName.'). Created',
                                      array('skill' => $skill, 'desc' => $skillDesc));
                return true;
            } else {
                $this->_log->addError('Skill ('.$skill.') did not yet exist in Category ('.$categoryName.'). Failed to save');
                return false;
            }
        } else {
            $this->_log->addWarning('Skill already exists in category: '.$categoryName,
                                    array('skill' => $skill, 'desc' => $skillDesc));
            return true;
        }
    }

    public function deleteSkillFromCategory($skill, $categoryName) {
        if (in_array($skill, $this->listSkillsInCategory($categoryName))) {
            unset($this->_skills[$categoryName]['skills'][$skill]);
            if ($this->save()) {
                $this->_log->addDebug('Skill ('.$skill.') removed from Category ('.$categoryName.'). Saved');
                return true;
            } else {
                $this->_log->addError('Skill ('.$skill.') removed from Category ('.$categoryName.'). Failed to save.');
                return false;
            }
        } else {
            $this->_log->addWarning('Skill ('.$skill.') did not exist in Category ('.$categoryName.'). Could not delete.',
                                    array('skill' => $this->_skill));
            return false;
        }
    }

    public function deleteCategory($categoryName, $force=FALSE) {
        if (count($this->_skills[$categoryName]['skills']) != 0 && $force === FALSE) {
            print "stap1";
            $this->_log->addWarning('Category ('.$categoryName.') has skills and force not set, not deleting.', 
                                    array('skills' => $this->_skills[$categoryName]));
            return false;
        } else if ($force === TRUE) {
            print "stap2";
            var_dump($this->_skills[$categoryName]);
            var_dump($this->_skills);
            print $categoryName;
            print isset($this->_skills[$categoryName]);
            unset($this->_skills[$categoryName]);
            if ($this->save()) {
                $this->_log->addDebug('Category ('.$categoryName.') has skills but force is set, deleted.', 
                                    array('skills' => $this->_skills[$categoryName]));
                return true;
            } else {
                $this->_log->addError('Category ('.$categoryName.') has skills but force is set, deleted. Could not save.', 
                                      array('skills' => $this->_skills[$categoryName]));
                return false;
            }
        } else if (count($this->_skills[$categoryName]['skills']) == 0) {
            print "stap3";
            unset($this->_skills[$categoryName]);
            if ($this->save()) {
                $this->_log->addDebug('Category ('.$categoryName.') has no skills, deleted.');
                return true;
            } else {
                $this->_log->addError('Category ('.$categoryName.') has no skills, deleted. Could not save.', 
                                      array('skills' => $this->_skills[$categoryName]));
                return false;
            }
        }
    }
}

?>
