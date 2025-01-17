<?php
// Определяем класс LegalEntity для работы с юридическими лицами
class LegalEntity {
    private $db; // Переменная для хранения соединения с базой данных

    // Конструктор класса, устанавливающий соединение с базой данных
    public function __construct($host, $user, $password, $dbname) {
        // Создаем новое соединение с базой данных MySQL
        $this->db = new mysqli('localhost', 'root', '', 'my_database');
        // Проверяем наличие ошибок при подключении
        if ($this->db->connect_error) {
            die("Connection failed: " . $this->db->connect_error); // Завершаем выполнение скрипта при ошибке
        }
    }

    // Метод для валидации данных перед их сохранением
    public function validate($data) {
        $errors = []; // Массив для хранения ошибок валидации

        // Проверяем, заполнено ли поле с названием компании
        if (empty($data['companyName'])) {
            $errors[] = 'Название компании обязательно.'; // Добавляем ошибку в массив
        }
        // Проверяем, соответствует ли ИНН формату (10 или 12 цифр)
        if (!preg_match('/^\d{10}$|^\d{12}$/', $data['inn'])) {
            $errors[] = 'ИНН должен быть 10 или 12 цифр.'; // Добавляем ошибку в массив
        }
        // Проверяем, начинается ли телефон с 11 и состоит ли из 11 цифр
        if (!preg_match('/^11\d{10}$/', $data['phone'])) {
            $errors[] = 'Телефон должен начинаться с 11 и содержать 11 цифр.'; // Добавляем ошибку в массив
        }
        // Проверяем, соответствует ли email правильному формату
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Неправильный формат email.'; // Добавляем ошибку в массив
        }

        return $errors; // Возвращаем массив ошибок
    }

    // Метод для сохранения данных юридического лица в базе данных
    public function save($data) {
        // Подготавливаем SQL-запрос для вставки данных
        $stmt = $this->db->prepare("INSERT INTO legal_entities (company_name, inn, phone, email) VALUES (?, ?, ?, ?)");
        // Привязываем параметры к подготовленному запросу
        $stmt->bind_param("ssss", $data['companyName'], $data['inn'], $data['phone'], $data['email']);
        return $stmt->execute(); // Выполняем запрос и возвращаем результат
    }

    // Метод для получения всех юридических лиц из базы данных
    public function getAll() {
        // Выполняем SQL-запрос для получения всех записей
        $result = $this->db->query("SELECT * FROM legal_entities");
        return $result->fetch_all(MYSQLI_ASSOC); // Возвращаем все записи в виде ассоциативного массива
    }
}

// Проверяем, был ли запрос методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Создаем новый экземпляр класса LegalEntity
    $entity = new LegalEntity('localhost', 'username', 'password', 'database');
    // Валидируем данные, полученные из POST-запроса
    $errors = $entity->validate($_POST);

    // Если ошибок валидации нет
    if (empty($errors)) {
        // Пытаемся сохранить данные в базе данных
        if ($entity->save($_POST)) {
            // Если сохранение успешно, возвращаем успешный ответ
            echo json_encode(['success' => true, 'message' => 'Регистрация успешна!']);
        } else {
            // Если произошла ошибка при сохранении данных
            echo json_encode(['success' => false, 'message' => 'Ошибка при сохранении данных.']);
        }
    } else {
        // Если есть ошибки валидации, возвращаем их в ответе
        echo json_encode(['success' => false, 'errors' => $errors]);
    }
}
?>

Найти еще