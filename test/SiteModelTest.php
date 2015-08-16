<?php

require_once(__DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php');

class SiteModelTest extends PanelTestCase {

  public function __construct() {

    parent::__construct();

    $this->removeContent();
    $this->removeAccounts();

    $this->user = $this->createAdmin();

  }

  protected function setUp() {
    $this->removeContent();
    $this->user->login('test');
  }

  protected function tearDown() {
    $this->user->logout();    
  }

  public function testBlueprint() {
    $this->assertInstanceOf('Kirby\\Panel\\Models\\Page\\Blueprint', $this->site->blueprint());
  }

  public function testUrl() {

    $this->assertEquals('/', $this->site->url());
    $this->assertEquals('/panel/options', $this->site->url('edit'));

  }

  public function testChanges() {
    $this->assertInstanceOf('Kirby\\Panel\\Models\\Page\\Changes', $this->site->changes());
  }

  public function testUpdate() {

    $this->site->update(array(
      'title' => 'Test Title'
    ));

    $this->assertEquals('Test Title', $this->site->title());

  }

  /**
   * @expectedException Exception
   * @expectedExceptionMessage The site cannot be deleted
   */
  public function testDelete() {
    $this->site->delete();
  }

}