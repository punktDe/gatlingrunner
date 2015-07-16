<?php
namespace PunktDe\Gatling\Domain\Model;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Daniel Lienert <daniel@lienert.cc>
 *
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use PunktDe\Gatling\Domain\Model\StatsProcessorProcessible;
use TYPO3\Flow\Utility\Files;

/**
 * Class Simulation
 *
 * @package PunktDe\Gatling\Domain\Model\Gatling
 */
class Simulation extends StatsProcessorProcessible {

	/**
	 * @var string
	 */
	protected $className;


	/**
	 * @var string
	 */
	protected $package;


	/**
	 * @var string
	 */
	protected $filePath;


	/**
	 * @var string
	 */
	protected $dataPath;


	/**
	 * @var string
	 */
	protected $reportRootPath;


	/**
	 * string
	 */
	protected $statsRootPath;


	/**
	 * @var string
	 */
	protected $simulationPath;


	/**
	 * @param string $className
	 */
	public function setClassName($className) {
		$this->className = $className;
	}

	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @param string $package
	 */
	public function setPackage($package) {
		$this->package = $package;
	}

	/**
	 * @return string
	 */
	public function getPackage() {
		return $this->package;
	}

	/**
	 * @param string $filePath
	 */
	public function setFilePath($filePath) {
		$this->filePath = $filePath;
	}

	/**
	 * @return string
	 */
	public function getFilePath() {
		return $this->filePath;
	}

	/**
	 * @param string $dataPath
	 */
	public function setDataPath($dataPath) {
		$this->dataPath = $dataPath;
	}


	/**
	 * @return string
	 */
	public function getReportFilePath() {
		return Files::concatenatePaths(array($this->reportPath, 'js/global_stats.json'));
	}

	/**
	 * @param string $simulationPath
	 */
	public function setSimulationPath($simulationPath) {
		$this->simulationPath = $simulationPath;
	}


	/**
	 * @return string
	 */
	public function getCombinedSimulationName() {
		return $this->package . '.' . $this->className;
	}


	/**
	 * @param string $reportRootPath
	 */
	public function setReportRootPath($reportRootPath) {
		$this->reportRootPath = $reportRootPath;
	}


	/**
	 * @return string
	 */
	public function getReportRootPath() {
		return $this->reportRootPath;
	}


	/**
	 * @param $gatlingPath
	 * @return string
	 */
	public function getRunCommand($gatlingPath) {
		return sprintf("%s -df %s -sf %s -rf %s -on %s -s %s", $gatlingPath, $this->dataPath, $this->simulationPath, $this->reportRootPath, $this->getCombinedSimulationName(),$this->getCombinedSimulationName());
	}
} 