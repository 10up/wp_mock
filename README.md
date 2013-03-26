WP_Mock
=======

WordPress API Mocking Framework

Use
--------

First, include the `WP_Mock` repository as a git submodule in your project.  Then, merely include the following code in your test boostrap file:

    require_once 'wp_mock/WP_Mock/Loader.php';

    $loader = new \WP_Mock\Loader;
    $loader->register();

Finally, register calls inside your test class to instantiate and clean up the `WP_Mock` object:

    class MyTestClass extends PHPUnit_Framework_TestCase {
        public function setUp() {
			\WP_Mock::setUp();
        }

        public function tearDown() {
			\WP_Mock::tearDown();
        }
    }

Write your tests as you normally would. If you desire specific responses from WordPress API calls, wire those specifically.

    class MyTestClass extends PHPUnit_Framework_TestCase {
        public function setUp() {
			\WP_Mock::setUp();
        }

        public function tearDown() {
			\WP_Mock::tearDown();
        }

        public function test_content_filter() {
            \WP_Mock::onFilter( 'the_content' )->with( 'Windows Rocks!' )->reply( 'Apple Rocks!' );

            $content = 'Windows Rocks!';

            $filtered = apply_filters( 'the_content', $content );

            $this->assertEquals( 'Apple Rocks!', $filtered );
        }

        /**
         * Our special_action function must actually invoke the 'special_action' action when it's done.
         * function special_action() {
         *     // ... do stuff
         *     do_action( 'special_action' );
         * }
         */
        public function test_method_has_action() {
            $hook_intercept = \Mockery::mock( 'intercept' );
            $hook_intercept->shouldReceive( 'intercepted' );
            \WP_Mock::onAction( 'special_action' )->with( null )->perform( array( $hook_intercept, 'intercepted' ) );

            // If this function does not call `do_action( 'special_action' )`, the test will fail.
            special_action();
        }
    }