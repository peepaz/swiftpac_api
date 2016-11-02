<?php

class Authentication
{
  /**
   * ActivationKey - This value is assigned during the 
   * customer go-live and certification process. 
   * The activation key is assigned to the developer unit.
   * @param string $activationKey
   */
  public function setActivationKey($activationKey) {
    $this->activationKey = $activationKey;
    return $this;
  }

  /**
   * ActivationKey - This value is assigned during the 
   * customer go-live and certification process. 
   * The activation key is assigned to the developer unit.
   * @return string
   */
  public function getActivationKey() {
    return $this->activationKey;
  }

  /**
   * LoginName - iabol shipper login name. The shipment 
   * will be lodged against this iabol account and the 
   * carrier account will be determined by this value 
   * and the service / carrier combination.
   * @param string $loginName
   */
  public function setLoginName($loginName) {
    $this->loginName = $loginName;
    return $this;
  }

  /**
   * LoginName - iabol shipper login name. The shipment 
   * will be lodged against this iabol account and the 
   * carrier account will be determined by this value 
   * and the service / carrier combination.
   * @return string
   */
  public function getLoginName() {
    return $this->loginName;
  }

  /**
   * Password - The password for the iabol account 
   * corresponding to the login name name.
   * @param string $password
   */
  public function setPassword($password) {
    $this->password = $password;
    return $this;
  }

  /**
   * Password - The password for the iabol account 
   * corresponding to the login name name.
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * AltID - Abol use only.
   * @param string $altID
   */
  public function setAltID($altID) {
    $this->altID = $altID;
    return $this;
  }

  /**
   * AltID - Abol use only.
   * @return string
   */
  public function getAltID() {
    return $this->altID;
  }

  public function toArray() {
    return array(
      'ActivationKey' => $this->getActivationKey(),
      'LoginName'     => $this->getLoginName(),
      'Password'      => $this->getPassword(),
      'AltID'         => $this->getAltID(),
    );
  }
}

?>