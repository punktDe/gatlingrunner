<?php
namespace PunktDe\Gatling\Processor;

 /***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Daniel Lienert <daniel@lienert.cc>
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
use \TYPO3\Flow\Utility\Files;
use TYPO3\Flow\Annotations as Flow;

/**
 * Stats Processor
 *
 * @package PunktDe\Gatling\Gatling
 */
class StatsProcessor {

	/**
	 * @var string
	 */
	protected $statsOutputPath;


	/**
	 * @var int
	 */
	protected $keepTrends = 100;


	/**
	 * @Flow\Inject
	 * @var \PunktDe\Gatling\Logger\LoggerInterface
	 */
	protected $logger;


	/**
	 * @param StatsProcessorProcessible $simulation
	 * @param string $statsOutputPath
	 */
	public function process($simulation, $statsOutputPath) {
		$this->statsOutputPath = $statsOutputPath;

		$this->copyStats($simulation);
		$this->calculateTrend($simulation);
	}



	/**
	 * @param StatsProcessorProcessible $simulation
	 */
	protected function copyStats(StatsProcessorProcessible $simulation) {
		$statsOutputDirectory = Files::concatenatePaths(array($this->statsOutputPath, $simulation->getCombinedSimulationName()));
		Files::createDirectoryRecursively($statsOutputDirectory);

		$statsSourcePath = $simulation->getReportFilePath();
		$statsTargetPath = Files::concatenatePaths(array($statsOutputDirectory, 'LastBuild.json'));

		$this->logger->log(sprintf("Copy global stats %s to %s\n", $statsSourcePath, $statsTargetPath));
		copy($statsSourcePath, $statsTargetPath);
	}



	/**
	 * @param StatsProcessorProcessible $simulation
	 */
	protected function calculateTrend(StatsProcessorProcessible $simulation) {
		$trendFilePath = Files::concatenatePaths(array($this->statsOutputPath, $simulation->getCombinedSimulationName(), 'Trend.json'));
		$lastBuildFilePath = $this->getLastBuildFilePath($simulation);

		$this->logger->log(sprintf("Combine %s to trend %s\n", $lastBuildFilePath, $trendFilePath));

		$lastBuildData = $this->readJson($lastBuildFilePath);
		$trendData = $this->readJson($trendFilePath);

		$trendData[] = $lastBuildData;
		$trendData = array_slice($trendData, ($this->keepTrends * -1), $this->keepTrends);

		$this->writeJson($trendFilePath, $trendData);
	}



	/**
	 * @param StatsProcessorProcessible $simulation
	 * @return integer
	 */
	public function getErrors(StatsProcessorProcessible $simulation) {
		$statsSourcePath = $simulation->getReportFilePath();
		$lastBuildData = $this->readJson($statsSourcePath);

		return (int) $lastBuildData['numberOfRequests']['ko'];
	}



	/**
	 * @param $jsonFilePath
	 * @return array|mixed
	 */
	protected function readJson($jsonFilePath) {
		$jsonData = array();

		if(file_exists($jsonFilePath)) {
			$data = file_get_contents($jsonFilePath);
			$jsonData = json_decode($data, TRUE);
		}

		return $jsonData;
	}



	/**
	 * @param $jsonFilePath
	 * @param array $jsonData
	 */
	protected function writeJson($jsonFilePath, array $jsonData) {
		$data = json_encode($jsonData);
		file_put_contents($jsonFilePath, $data);
	}



	/**
	 * @param StatsProcessorProcessible $simulation
	 * @return string
	 */
	protected function getLastBuildFilePath(StatsProcessorProcessible $simulation) {
		return Files::concatenatePaths(array($this->statsOutputPath, $simulation->getCombinedSimulationName(), 'LastBuild.json'));
	}

} 