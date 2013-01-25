<?php
namespace fancycv;
use Symfony\Component\Yaml\Yaml;

class Categories
{
	private $_skills;
	private $_skillsFile;
    public function __construct() {
    	$this->_skillsFile = DATA_DIR.'skills.yml';
        if (file_exists($this->_skillsFile)) {
            $this->_skills = Yaml::parse($this->_skillsFile);
            var_dump($this->_skills);
        } else {
            $this->_skills = array();
        }
    }

    public function save() {
    	return Helpers::createFile(Yaml::dump($this->_skills), 'skills.yml', $this->_skillsFile);
    }

    public function newCategory($categoryName, $categoryDesc=NULL) {
    	if (!in_array($categoryName, $this->_skills)) {
    		$this->_skills[$categoryName] = array('desc' => NULL, 'skills' => array());
    		if ($categoryDesc != NULL) {
    			$this->_skills[$categoryName]['desc'] = $categoryDesc;
    		}
    		$this->save();
    	}
    	return $categoryName;
    }

    public function listCategories() {
    	print_r(array_keys($this->_skills));
    	return array_keys($this->_skills);
    }

    public function addSkillToCategory($skill, $categoryName) {
    	$this->_skills[$categoryName]['skills'][] = $skill;
    	return $this->save();
    }
}

?>
