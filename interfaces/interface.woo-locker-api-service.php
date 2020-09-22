<?php

interface WooLockerApiServiceInterface {

	public function getAvailabilities($frequence);

	public function setLockerSession($locker);

	public function saveOrder($order);

	public function updateOrder($order);

	public function cancelOrder($order);

}