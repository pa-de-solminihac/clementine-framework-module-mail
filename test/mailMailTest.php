<?php
class mailMailTest extends mailMailTest_Parent
{
    private static $Clementine;

    /**
     * List of all mailers used for sending an email.
     * Allows knowing which fallbacks were used.
     */
    private static $lastMailerPath;

    public static function addMailerToPath($mailer) {
        array_push(self::$lastMailerPath, $mailer);
    }

    protected function sendWithMailer($mailer = 'mailtestalwayssucceed') {
        self::$lastMailerPath = array();
        $params = array(
            'mailer' => $mailer,
            'to' => 'alain@quai13.com',
            'title' => 'Test',
            'from' => 'alain@quai13.com',
            'message_html' => 'Test',
            'toname' => 'Test Mailer',
        );
        return self::$Clementine->getHelper('mail')->send($params);
    }

    public static function setUpBeforeClass() {
        global $Clementine;
        self::$Clementine = $Clementine;
        return parent::setUpBeforeClass();
    }

    public function testNofallback() {
        // Simple success, don't use fallbacks
        $this->assertTrue($this->sendWithMailer('mailtestalwayssucceed'));
        $this->assertEquals(array('mailtestalwayssucceed'), self::$lastMailerPath);

        // Simple failure, don't use fallbacks
        $this->assertFalse($this->sendWithMailer('mailtestalwaysfail'));
        $this->assertEquals(array('mailtestalwaysfail'), self::$lastMailerPath);
    }

    public function testSimpleFallback() {
        // Failure, fallback to success
        $this->assertTrue($this->sendWithMailer('mailtestfailthenfallback'));
        $this->assertEquals(array('mailtestfailthenfallback', 'mailtestalwayssucceed'), self::$lastMailerPath);

        // Success, shouldn't fall back to anything
        $this->assertTrue($this->sendWithMailer('mailtestsuccesswithfallback'));
        $this->assertEquals(array('mailtestsuccesswithfallback'), self::$lastMailerPath);
    }

    public function testComplexFallback() {
        // Failure, fallback to failure, fallback to success
        $this->assertTrue($this->sendWithMailer('mailtestfallbacktofallback'));
        $this->assertEquals(array('mailtestfallbacktofallback', 'mailtestfailthenfallback', 'mailtestalwayssucceed'), self::$lastMailerPath);

        // Failure, fallback to failure, fallback to failure
        $this->assertFalse($this->sendWithMailer('mailtestfallbacktofallbackfail'));
        $this->assertEquals(array('mailtestfallbacktofallbackfail', 'mailtestfailthenfallbackfail', 'mailtestalwaysfail'), self::$lastMailerPath);

        // Failure, fallback to failure twice, mailers shouldn't be run twice
        $this->assertFalse($this->sendWithMailer('mailtestfailwithduplicatefailurefallback'));
        $this->assertEquals(array('mailtestfailwithduplicatefailurefallback', 'mailtestalwaysfail'), self::$lastMailerPath);

        // Failure, fallback to success twice, mailers shouldn't be run twice
        $this->assertTrue($this->sendWithMailer('mailtestfailwithduplicatesuccessfallback'));
        $this->assertEquals(array('mailtestfailwithduplicatesuccessfallback', 'mailtestalwayssucceed'), self::$lastMailerPath);

        // Failure, fallback to self, mailers shouldn't be run twice
        $this->assertFalse($this->sendWithMailer('mailtestfailwithfallbacktoself'));
        $this->assertEquals(array('mailtestfailwithfallbacktoself'), self::$lastMailerPath);

        // Failure, fallback to failure to failure, then fallback to failure success
        $this->assertTrue($this->sendWithMailer('mailtestmultilevelfallback'));
        $this->assertEquals(array(
            'mailtestmultilevelfallback',
            'mailtestfallbacktofallbackfail',
            'mailtestfailthenfallbackfail',
            'mailtestalwaysfail',
            'mailtestfallbacktofallback',
            'mailtestfailthenfallback',
            'mailtestalwayssucceed',
        ), self::$lastMailerPath);
    }
}

// Mocks
class MailtestAlwaysSucceedHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestalwayssucceed');
        return true;
    }

    public function getFallback() { return array(); }
}

class MailtestAlwaysFailHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestalwaysfail');
        return false;
    }

    public function getFallback() { return array(); }
}

class MailtestSuccessWithFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestsuccesswithfallback');
        return true;
    }

    public function getFallback() { return array('mailtestalwaysfail'); }
}

class MailtestFailThenFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfailthenfallback');
        return false;
    }

    public function getFallback() { return array('mailtestalwayssucceed'); }
}

class MailtestFailThenFallbackFailHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfailthenfallbackfail');
        return false;
    }

    public function getFallback() { return array('mailtestalwaysfail'); }
}

class MailtestFallbackToFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfallbacktofallback');
        return false;
    }

    public function getFallback() { return array('mailtestfailthenfallback'); }
}

class MailtestFallbackToFallbackFailHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfallbacktofallbackfail');
        return false;
    }

    public function getFallback() { return array('mailtestfailthenfallbackfail'); }
}

class MailtestFailWithDuplicateFailureFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfailwithduplicatefailurefallback');
        return false;
    }

    public function getFallback() { return array('mailtestalwaysfail', 'mailtestalwaysfail'); }
}

class MailtestFailWithDuplicateSuccessFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfailwithduplicatesuccessfallback');
        return false;
    }

    public function getFallback() { return array('mailtestalwayssucceed', 'mailtestalwayssucceed'); }
}

class MailtestFailWithFallbackToSelfHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestfailwithfallbacktoself');
        return false;
    }

    public function getFallback() { return array('mailtestfailwithfallbacktoself'); }
}

class MailtestMultiLevelFallbackHelper {
    public function send($params) {
        mailMailTest::addMailerToPath('mailtestmultilevelfallback');
        return false;
    }

    public function getFallback() { return array('mailtestfallbacktofallbackfail', 'mailtestfallbacktofallback'); }
}
