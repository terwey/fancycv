<?php
namespace fancycv;
use Symfony\Component\Yaml\Yaml;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Positions
{
    private $_log;
	private $_positions;
	private $_positionsFile;
    public function __construct() {
        $this->_log = new Logger('Positions');
        $this->_log->pushHandler(new StreamHandler(LOG_DIR.'positions.log'), 
                                 Logger::WARNING);
    	$this->_positionsFile = DATA_DIR.'Positions.yml';
        if (file_exists($this->_positionsFile)) {
            $this->_log->addDebug(__FUNCTION__.': Positions file exists. Parsing.', 
                                  array('filename' => $this->_positionsFile));
            $this->_positions = Yaml::parse($this->_positionsFile);
            $this->_log->addDebug(__FUNCTION__.': Positions file parsed.', 
                                  array('filename' => $this->_positionsFile,
                                        'parsedContents'=> $this->_positions));
        } else {
            $this->_log->addDebug(__FUNCTION__.': Positions file did not exist, creating empty array.');
            $this->_positions = array();
        }
    }

    public function save() {
        $status = Helpers::createFile(Yaml::dump($this->_positions, 3), // 2nd option in the Yaml::dump
                                      'Positions.yml',                  // defines the inline switch, 
                                      $this->_positionsFile);           // 3 keeps it tidy
        if (!$status) {
            $this->_log->addError(__FUNCTION__.': Did not save.', 
                                  array('filename'=>$this->_positionsFile));
        } else {
            $this->_log->addDebug(__FUNCTION__.': Saved.', 
                                  array('filename'=>$this->_positionsFile, 'array'=>$this->_positions));
        }
        return $status;
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function newType($typeName, $typeDesc=NULL) {
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
    	if (!in_array($typeName, $this->listTypes())) {
    		$this->_positions[$typeName] = array('desc' => $typeDesc, 
                                                  'Positions' => array());
    		if ($this->save()) {
                $this->_log->addDebug(__FUNCTION__.': Type ('.$typeName.') did not yet exist. Created.');
                return true;
            } else {
                $this->_log->addError(__FUNCTION__.': Type ('.$typeName.') did not yet exist. Failed to save');
                return false;
            }
    	} else {
            $this->_log->addWarning(__FUNCTION__.': Type already exists: '.$typeName);
            return true;
        }
    }

    /**
     * @return array array of existing Types
     **/
    public function listTypes() {
        if (is_array($this->_positions)) {
    	   return array_keys($this->_positions);
        } else {
            return array();
        }
    }

    /**
     * @return array array of existing Positions in Type or empty if the Type doesn't exist
     **/
    public function listPositionsInType($typeName) {
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
        if (isset($this->_positions[$typeName])) {
            return array_keys($this->_positions[$typeName]['Positions']);
        } else {
            return array();
        }
    }

     /**
     * @return array array of all Positions in all Types, returned as $employer->type
     **/
     public function listPositions() {
        $positionsByType = array();
        foreach ($this->listTypes() as $key => $type) {
            foreach ($this->listPositionsInType($type) as $key => $employer) {
                $positionsByType[$employer][] = $type;
            }
        }
        return $positionsByType;
     }

    /**
     * @return BOOL Indicates success.
     **/
    public function addPositionToType($typeName, $periodFrom, $periodTo, $employer, $title, $summary, $skills=array()) {
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
        if (empty($periodFrom)) { throw new \InvalidArgumentException('$periodFrom cannot be empty'); }
        if (empty($periodTo)) { throw new \InvalidArgumentException('$periodTo cannot be empty'); }
        if (empty($employer)) { throw new \InvalidArgumentException('$employer cannot be empty'); }
        if (empty($title)) { throw new \InvalidArgumentException('$title cannot be empty'); }
        if (empty($summary)) { throw new \InvalidArgumentException('$summary cannot be empty'); }
        if (!in_array($typeName, $this->listTypes())) {
            $this->newType($typeName);
        }
        if (!in_array($employer, $this->listPositionsInType($typeName))) {
            if (!is_array($skills)) {
                $skills = array();
            }
            $positionArray = array(
                'periodFrom' => $periodFrom,
                'periodTo' => $periodTo,
                'employer' => $employer,
                'title' => $title,
                'summary' => $summary,
                'skills' => $skills // this HAS to become an array of skills that apply IN CATEGORIES
                );
            $this->_positions[$typeName]['Positions'][$employer] = $positionArray;
            if ($this->save()) {
                $this->_log->addDebug(__FUNCTION__.': Position (for '.$employer.') did not yet exist in Type ('.$typeName.'). Created',
                                      array('Employer' => $employer, 'array' => $positionArray));
                return true;
            } else {
                $this->_log->addError(__FUNCTION__.': Position ( for'.$employer.') did not yet exist in Type ('.$typeName.'). Failed to save',
                                      array('Employer' => $employer, 'array' => $positionArray));
                return false;
            }
        } else {
            $this->_log->addWarning(__FUNCTION__.': Position already exists in Type: '.$typeName);
            return true;
        }
    }

    public function addPositionToTypeWithArray($typeName, $positionArray) {
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
        if (!is_array($positionArray)) { throw new \InvalidArgumentException('$positionArray has to be an array'); }
        return $this->addPositionToType($typeName, $positionArray['periodFrom'], $positionArray['periodTo'], $positionArray['employer'], $positionArray['title'], $positionArray['summary'], $positionArray['skills']);
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function deletePositionFromType($position, $typeName) {
        if (empty($position)) { throw new \InvalidArgumentException('$position cannot be empty'); }
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
        if (in_array($position, $this->listPositionsInType($typeName))) {
            unset($this->_positions[$typeName]['Positions'][$position]);
            if ($this->save()) {
                $this->_log->addDebug(__FUNCTION__.': Position ('.$position.') removed from Type ('.$typeName.'). Saved');
                return true;
            } else {
                $this->_log->addError(__FUNCTION__.': Position ('.$position.') removed from Type ('.$typeName.'). Failed to save.');
                return false;
            }
        } else {
            $this->_log->addWarning(__FUNCTION__.': Position ('.$position.') did not exist in Type ('.$typeName.'). Could not delete.',
                                    array('Position' => $this->_Position));
            return false;
        }
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function deleteType($typeName, $force=FALSE) {
        if (empty($typeName)) { throw new \InvalidArgumentException('$typeName cannot be empty'); }
        if (count($this->_positions[$typeName]['Positions']) != 0 && $force === FALSE) {
            $this->_log->addWarning(__FUNCTION__.': Type ('.$typeName.') has Positions and force not set, not deleting.', 
                                    array('Positions' => $this->_positions[$typeName]));
            return false;
        } else if ($force === TRUE) {
            unset($this->_positions[$typeName]);
            if ($this->save()) {
                $this->_log->addDebug(__FUNCTION__.': Type ('.$typeName.') has Positions but force is set, deleted.');
                return true;
            } else {
                $this->_log->addError(__FUNCTION__.': Type ('.$typeName.') has Positions but force is set, deleted. Could not save.');
                return false;
            }
        } else if (count($this->_positions[$typeName]['Positions']) == 0) {
            unset($this->_positions[$typeName]);
            if ($this->save()) {
                $this->_log->addDebug(__FUNCTION__.': Type ('.$typeName.') has no Positions, deleted.');
                return true;
            } else {
                $this->_log->addError(__FUNCTION__.': Type ('.$typeName.') has no Positions, deleted. Could not save.', 
                                      array('Positions' => $this->_positions[$typeName]));
                return false;
            }
        }
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function movePositionToType($employer, $currentType, $targetType) {
        if (empty($employer)) { throw new \InvalidArgumentException('$employer cannot be empty'); }
        if (empty($currentType)) { throw new \InvalidArgumentException('$currentType cannot be empty'); }
        if (empty($targetType)) { throw new \InvalidArgumentException('$targetType cannot be empty'); }

        if (in_array($currentType, $this->listTypes())) {
            if (in_array($targetType, $this->listTypes())) {
                if (in_array($employer, $this->listPositionsInType($currentType))) {
                    $positionArray = $this->_positions[$currentType]['Positions'][$employer];
                    
                    if ($this->addPositionToTypeWithArray($targetType, $positionArray) 
                        && $this->deletePositionFromType($employer, $currentType)) {
                        $this->_log->addDebug(__FUNCTION__.': Position ('.$employer.') move to Type ('.$targetType.') succeeded. Saved');
                        return true;
                    } else {
                        $this->_log->addError(__FUNCTION__.': Position ('.$employer.') move to Type ('.$targetType.') succeeded. Failed to save');
                        return false;
                    }
                } else {
                    $this->_log->addError(__FUNCTION__.': Position ('.$employer.') does not exist in Type ('.$currentType.').');
                    return false;
                }
            } else {
                $this->_log->addError(__FUNCTION__.': Type ('.$targetType.') target does not exist.');
                return false;
            }
        } else {
            $this->_log->addError(__FUNCTION__.': Type ('.$currentType.') does not exist.');
            return false;
        }
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function addSkillToPosition($employer, array $skill) {
        if (empty($employer)) { throw new \InvalidArgumentException('$employer cannot be empty'); }
        if (!is_array($skill)) { throw new \InvalidArgumentException('$skill has to be an array'); }

        $currentPositions = $this->listPositions();
        $employers = array_keys($currentPositions);
        if (in_array($employer, $employers)) {
            $type = $currentPositions[$employers[array_search($employer, $employers)]][0];
            if (!in_array(key($skill), $this->_positions[$type]['Positions'][$employer]['skills'])) {
                $this->_positions[$type]['Positions'][$employer]['skills'][key($skill)] = $skill[key($skill)];
                if ($this->save()) {
                    $this->_log->addDebug(__FUNCTION__.': Skill ('.key($skill).') did not yet exist. Created.');
                    return true;
                } else {
                    $this->_log->addError(__FUNCTION__.': Skill ('.key($skill).') did not yet exist. Failed to save');
                    return false;
                }
            } else {
                $this->_log->addWarning(__FUNCTION__.': Skill already exists: '.key($skill));
                return true;
            }
            
        } else {
            $this->_log->addError(__FUNCTION__.': Position at: '.$employer.' does not exist. Cannot add Skill');
            throw new \InvalidArgumentException('Position at: '.$employer.' does not exist. Cannot add Skill');
            return false;
        }
    }

    /**
     * @return array skills
     **/
    public function getSkillsFromPosition($employer) {
        if (empty($employer)) { throw new \InvalidArgumentException('$employer cannot be empty'); }

        $currentPositions = $this->listPositions();
        $employers = array_keys($currentPositions);
        if (in_array($employer, $employers)) {
            $type = $currentPositions[$employers[array_search($employer, $employers)]][0];
            return $this->_positions[$type]['Positions'][$employer]['skills'];
        } else {
            $this->_log->addError(__FUNCTION__.': Position at: '.$employer.' does not exist. Cannot get Skills');
            throw new \InvalidArgumentException('Position at: '.$employer.' does not exist. Cannot get Skills');
            return false;
        }
    }

    /**
     * @return BOOL Indicates success.
     **/
    public function deleteSkillFromPosition($employer, $skillName) {
        if (empty($employer)) { throw new \InvalidArgumentException('$employer cannot be empty'); }
        if (empty($skillName)) { throw new \InvalidArgumentException('$skillName cannot be empty'); }

        $currentPositions = $this->listPositions();
        $employers = array_keys($currentPositions);
        if (in_array($employer, $employers)) {
            $type = $currentPositions[$employers[array_search($employer, $employers)]][0];
            if (!in_array($skillName, $this->_positions[$type]['Positions'][$employer]['skills'])) {
                unset($this->_positions[$type]['Positions'][$employer]['skills'][$skillName]);
                if ($this->save()) {
                    $this->_log->addDebug(__FUNCTION__.': Skill ('.$skillName.') has been deleted.');
                    return true;
                } else {
                    $this->_log->addError(__FUNCTION__.': Skill ('.$skillName.') has been deleted. Failed to save');
                    return false;
                }
            } else {
                $this->_log->addError(__FUNCTION__.': Position at: '.$employer.' does not have Skill: '.$skillName);
                throw new \InvalidArgumentException('Position at: '.$employer.' does not have Skill: '.$skillName);
                return false;
            }
            
        } else {
            $this->_log->addError(__FUNCTION__.': Position at: '.$employer.' does not exist. Cannot delete Skill');
            throw new \InvalidArgumentException('Position at: '.$employer.' does not exist. Cannot delete Skill');
            return false;
        }
    }
}

?>
