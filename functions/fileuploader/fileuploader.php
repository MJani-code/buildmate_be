<?php
require_once('../../functions/interface/fileuploaderinterface.php');
require('../../inc/conn.php');

// FileUploader.php
class FileUploader implements FileUploaderInterface
{

    private $conn;
    private $DOC_URL;
    private $DOC_PATH;
    public function __construct($conn, $DOC_URL, $DOC_PATH)
    {
        $this->conn = $conn;
        $this->DOC_URL = $DOC_URL;
        $this->DOC_PATH = $DOC_PATH;
    }

    public function uploadFile($file, $title, $type, $type_id, $userid, $createdby)
    {
        try {
            $fileName = $file['name'];
            $fileTmpName = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            $fileType = $file['type'];

            $fileExt = explode('.', $fileName);
            $fileActualExt = strtolower(end($fileExt));

            $statusid = ($type_id === 2) ? 1 : 5;
            $status = ($type_id === 2) ? 'nyitott' : 'érvényes';

            $allowed = array('jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx');
            // $fileDestination = '../../uploads/'.$type.'/'.$fileName;
            $fileDestination = $this->DOC_PATH . '/uploads/' . $type . '/' . $fileName;
            $fileUrl = $this->DOC_URL . '/uploads/' . $type . '/' . $fileName;
            $error = "";
            $response = "";

            if (in_array($fileActualExt, $allowed)) {
                if ($fileError === 0) {
                    if ($fileSize < 10000000) {
                        if (file_exists($fileDestination)) {
                            $error .= "A fájl már létezik";
                        } else {
                            move_uploaded_file($fileTmpName, $fileDestination);

                            $sql = "SELECT MAX(id) as max_id FROM documents";
                            $stmt = $this->conn->query($sql);
                            $result = $stmt->fetch(PDO::FETCH_ASSOC);

                            $maxId = $result['max_id'];
                            $newId = $maxId + 1;

                            $now = date("Y-m-d H:i:s");

                            $stmt = $this->conn->prepare(
                                "INSERT INTO documents
                            (id_status, id_type, title, filename, path, url, created_at, created_by)
                            VALUES (:id_status, :id_type, :title, :filename, :path, :url, :created_at, :created_by);
                        "
                            );

                            $stmt->bindParam(":id_status", $statusid);
                            $stmt->bindParam(":id_type", $type_id);
                            $stmt->bindParam(":title", $title);
                            $stmt->bindParam(":filename", $fileName);
                            $stmt->bindParam(":path", $fileDestination);
                            $stmt->bindParam(":url", $fileUrl);
                            $stmt->bindParam(":created_at", $now);
                            $stmt->bindParam(":created_by", $userid);
                            $stmt->execute();
                            $rowCount = $stmt->rowCount();

                            if ($rowCount > 0) {
                                $response = array(
                                    'result' => array(
                                        'id' => $newId,
                                        'title' => $title,
                                        'createdAt' => $now,
                                        'createdBy' => $createdby,
                                        'path' => $fileDestination,
                                        'url' => $fileUrl,
                                        'typeId' => (int)$type_id,
                                        'type' => $type,
                                        'statusId' => $statusid,
                                        'status' => $status,
                                    ),
                                    'confirm' => true,
                                );
                                echo json_encode($response);
                            }
                        }
                    } else {
                        $error .= "A fájl mérete túl nagy!";
                    }
                } else {
                    $error .= "Hiba történt a fájl feltöltése közben. Kérlek fordulj a rendszergazdához! Hiba: " . $fileError . " ";
                }
            } else {
                $error .= "Ez a fájltípus nem engedélyezett!";
            }

            // return array('error' => $error, 'response' => $response);
            echo $error;

        } catch (Exception $e) {
            $error = $e->getMessage();
            echo json_encode($error);
        }

    }
}

?>