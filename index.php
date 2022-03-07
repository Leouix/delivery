<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

//echo 'ok';

include_once 'DB-config.php';

if( empty($db_connection['hostname']) || empty($db_connection['username']) || empty($db_connection['database'])) {
    ?>

    <div class="alert alert-warning" role="alert">
        Укажите подключение к базе данных
    </div>

    <?php
}

require_once 'Calculate_Delivery.php';

?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">

<?php

$sourceKladr = $_POST['sourceKladr'] ?? '';
$targetKladr = $_POST['targetKladr'] ?? '';
$weight = $_POST['weight'] ?? 0;

$delivery = new Delivery_Companies( $db_connection );

?>

<div class="container">

    <?php

    if( isset($_POST['add_company']) && !empty($_POST['add_company'])) {
        $delivery->addDeliveryCompany();
    }

    ?>

    <?= $delivery->getFormForRequest() ?>

    <hr>

    <?php $delivery->displayListDeliveries( $sourceKladr, $targetKladr, $weight ); ?>

    <hr>

    <div class="add-company">
        <h3 class="add-company-title hidden">Добавить компанию-перевозчика</h3>
        <form class="add-company-form" method="POST">
            <div class="mb-3">
                <label for="company-name" class="form-label">Company Name</label>
                <input type="text" name="company_name" class="form-control" id="company-name">
            </div>
            <div class="mb-3">
                <label for="price-label" class="form-label">Поле ответа json компании, соответствующее цене перевозки</label>
                <input type="text" name="price_label" class="form-control" id="price-label">
            </div>
            <div class="mb-3">
                <label for="date-label" class="form-label">Поле ответа json компании, соответствующее времени перевозки</label>
                <input type="text" name="date_label" class="form-control" id="date-label">
            </div>
            <div class="mb-3">
                <label for="error-label" class="form-label">Поле ответа json компании, соответствующее ошибке</label>
                <input type="text" name="error_label" class="form-control" id="error-label">
            </div>
            <button type="submit" name="add_company" class="btn btn-primary" value="1">Save</button>
        </form>
    </div>

</div>

<style>
    .container {
        padding: 35px 0;
    }
    .table {
        margin-top: 25px;
    }
    .add-company-form {
        display: none;
    }
    .add-company-title {
        cursor: pointer;
    }
    .add-company-title.hidden:after {
        content: '+';
        display: inline-block;
        margin: 0 5px;
        color: blue;
    }
    .add-company-title.open:after {
        content: '-';
        display: inline-block;
        margin: 0 5px;
        color: #6565d3;
    }
</style>

<script>
    var addCompanyForm = document.querySelector('.add-company-form');
    var addCompanyTitle = document.querySelector('.add-company-title');

    addCompanyTitle.addEventListener('click', function(){
        if( this.classList.contains('hidden') ) {
            addCompanyForm.style.display = 'block';
            this.classList.remove('hidden');
            this.classList.add('open');
        } else {
            addCompanyForm.style.display = 'none';
            this.classList.add('hidden');
            this.classList.remove('open');
        }
    })
</script>

<?php