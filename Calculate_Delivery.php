<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

class Delivery_Companies {

    private string $sourceKladr;
    private string $targetKladr;
    private float $weight;
    public string $error;

    private $mysqli;
    public $list_companies = [];
    public $delivery_companies_schemes = [];

    public function __construct( $db_connect ) {
        $this->hostname = $db_connect['hostname'];
        $this->username = $db_connect['username'];
        $this->password = $db_connect['password'];
        $this->database = $db_connect['database'];

        $this->classInitial();
    }

    private function classInitial() {

        $this->mysqli = mysqli_connect($this->hostname,$this->username,$this->password, $this->database);

        if ($this->mysqli->connect_error) {
            die("Connection failed: " . $this->mysqli->connect_error);
        }

        if( ! $this->is_delivery_table_exist() ) {
            $this->createDBTableDeliveryCompanies();
            $this->setDefaultTestCompanies();
        }

    }

    private function is_delivery_table_exist() {

        $checktable = mysqli_query($this->mysqli, "SHOW TABLES LIKE '%delivery_companies%'");
        $result = mysqli_num_rows($checktable) > 0;

        return $result;
    }

    private function createDBTableDeliveryCompanies() {

        $sql = "CREATE TABLE delivery_companies (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_name VARCHAR(30) NOT NULL,
            price VARCHAR(30) NOT NULL,
            date VARCHAR(30) NOT NULL,
            error TEXT
            )";

        if ( $this->mysqli->query($sql) ) {
            echo 'create table is succesfully';
        } else {
            echo 'something is wrong';
        }

    }

    private function setDefaultTestCompanies() {
        // insert test companies to DB

        $company_name_1 = 'Fast-delivery';
        $price_1 = 'price';
        $date_1 = 'period';
        $error_1 = 'error';

        $this->insertDelivCompanyInDB ( $company_name_1, $price_1, $date_1, $error_1 );

        $company_name_2 = 'Slow-delivery';
        $price_2 = 'coefficient';
        $date_2 = 'date';
        $error_2 = 'error';

        $this->insertDelivCompanyInDB ( $company_name_2, $price_2, $date_2, $error_2 );

    }

    private function insertDelivCompanyInDB ( $company_name, $price, $date, $error ) {

        $sql = "INSERT INTO `delivery_companies` (`id`, `company_name`, `price`, `date`, `error`) VALUES ('', '$company_name', '$price', '$date', '$error')";

        $this->mysqli->query($sql);

    }

    protected function formatDataCompanyToBeShould() {

        $this->getSchemesDeliveryCompanies();
        $result_array = [];

        if( !empty($this->list_companies)) {
            foreach( $this->list_companies as $company ) {

                if( $company == 'Fast-delivery' ) {
                    $data = $this->getFastDeliveryPrices() ?? false;
                    if( $data ) {
                        $data_array = json_decode($data, true);
                        $data_array['company_name'] = $company;
                        $result_array[] = $this->dataConvert($data_array);
                    }
                }

                else if( $company == 'Slow-delivery' ) {
                    $data = $this->getSlowDeliveryPrices() ?? false;
                    if( $data ) {
                        $data_array = json_decode($data, true);
                        $data_array['company_name'] = $company;
                        $result_array[] = $this->dataConvert($data_array);
                    }

                }

                else {
                    $data = $this->getTestDeliveryPrices( $company ) ?? false;

                    if( $data ) {
                        $data_array = json_decode($data, true);
                        $data_array['company_name'] = $company;
                        $result_array[] = $this->dataConvert($data_array);
                    }
                }

            }
        }

        return $result_array;

    }

    private function dataConvert( $data_array ) {

        $company_name = $data_array['company_name'];

        $result_array['company_name'] = $company_name;
        $result_array['price'] = $data_array[$this->delivery_companies_schemes[$company_name]['price']];
        $result_array['date'] = $data_array[$this->delivery_companies_schemes[$company_name]['date']];
        $result_array['error'] = $data_array[$this->delivery_companies_schemes[$company_name]['error']];
        return $result_array;
    }

    private function getTestDeliveryPrices( $company_name ) {

        if(empty($this->sourceKladr) || empty($this->targetKladr) || $this->weight == 0) return [];

        $sql = "SELECT * FROM `delivery_companies` WHERE `company_name` = '$company_name'";
        $result = $this->mysqli->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $price = $row['price'];
                $date = $row['date'];
                $error = $row['error'];
            }
        }

        $array = [
            $price => round(rand(1000, 9900) / 100, 2),
            $date  => rand(3, 15),
            $error => ''
        ];

        return json_encode($array);
    }

    protected function getFastDeliveryPrices() {

        $sourceKladr = $this->sourceKladr;
        $targetKladr = $this->targetKladr;
        $weight = $this->weight;

        if(empty($sourceKladr) || empty($targetKladr) || $weight == 0) return [];

        $array = [
            'price' => round(rand(1000, 9900) / 100, 2),
            'period' => rand(3, 15),
            'error' => ''
        ];

        return json_encode($array);

    }

    protected function getSlowDeliveryPrices() {

        $sourceKladr = $this->sourceKladr;
        $targetKladr = $this->targetKladr;
        $weight = $this->weight;

        if(empty($sourceKladr) || empty($targetKladr) || $weight == 0) return [];

        $array = [
            'coefficient' => round(rand(1000, 9900) / 100, 2),
            'date' => rand(3, 15),
            'error' => ''
        ];

        return json_encode($array);

    }

    public function displayListDeliveries( string $sourceKladr='', string $targetKladr='', float $weight=0 ) {

        if(empty($sourceKladr) || empty($targetKladr) || $weight == 0) {
            $this->error = 'Укажите данные для расчета';
            echo $this->getFormErrors();
        }

        $this->sourceKladr = $sourceKladr;
        $this->targetKladr = $targetKladr;
        $this->weight = $weight;

        $list_companies = $this->formatDataCompanyToBeShould();

        if( !empty($list_companies) ) {
            ?>
            <h3>Possible delivery methods</h3>
            <table class="table">
                <tr>
                    <th scope="col">Company Name</th>
                    <th scope="col">Price</th>
                    <th scope="col">Date</th>
                </tr>

                <?php
                foreach( $list_companies as $delive_company ) {

                    ?>

                    <tr>
                        <td><?=$delive_company['company_name'] ?? ''?></td>
                        <td><?=$delive_company['price'] ?? ''?></td>
                        <td><?=$delive_company['date'] ?? ''?></td>
                    </tr>

                    <?php
                }
                ?>

            </table>
            <?php
        }

        mysqli_close($this->mysqli);

    }

    public function getFormErrors() {

        if( isset($_POST['form_request']) && !empty($this->error ) ) {

            ob_start();
            ?>

            <div class="alert alert-danger" role="alert">
                <?= $this->error?>
            </div>

            <?php
            return ob_get_clean();
        }
    }

    public function getFormForRequest() {
        ob_start();

        $sourceKladr = $_POST['sourceKladr'] ?? '';
        $targetKladr = $_POST['targetKladr'] ?? '';
        $weight = $_POST['weight'] ?? 0;

        ?>

        <h3>Рассчитать доставку:</h3>
        <form action='' method="POST">
            <div class="mb-3">
                <label for="sourceKladr" class="form-label">From: </label>
                <input type="text" name="sourceKladr" class="form-control" id="sourceKladr" value="<?=$sourceKladr?>">
            </div>
            <div class="mb-3">
                <label for="targetKladr" class="form-label">To: </label>
                <input type="text" name="targetKladr"  class="form-control" id="targetKladr" value="<?=$targetKladr?>">
            </div>
            <div class="mb-3">
                <label for="weight" class="form-label">Weight: </label>
                <input type="number" name="weight" class="form-label" id="weight" value="<?=$weight?>">
            </div>
            <button type="submit" name="form_request" class="btn btn-primary">Submit</button>
        </form>

        <?php
        return ob_get_clean();

    }

    public function addDeliveryCompany() {

        $company_name = $_POST['company_name'] ?? '';
        $price_label = $_POST['price_label'] ?? '';
        $date_label = $_POST['date_label'] ?? '';
        $error_label = $_POST['error_label'] ?? '';

        $this->insertDelivCompanyInDB ( $company_name, $price_label, $date_label, $error_label );

    }

    public function getSchemesDeliveryCompanies() {

        $sql = 'SELECT * FROM `delivery_companies`';
        $result = $this->mysqli->query($sql);

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $this->delivery_companies_schemes[$row['company_name']] = $row;
                $this->list_companies[] = $row['company_name'];
            }
        } else {
            echo "0 results";
        }

    }

}

