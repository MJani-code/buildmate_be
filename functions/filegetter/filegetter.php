<?php
require_once('../../functions/interface/filegetterinterface.php');
require('../../inc/conn.php');
// FileUploader.php
class FileGetter implements FileGetterInterface
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getFile($id)
    {

        try {
            $stmt = $this->conn->prepare(
                "SELECT
                d.id as 'id',
                d.title as 'title',
                d.created_at as 'createdAt',
                CONCAT(u.last_name,' ',u.first_name) as 'createdBy',
                d.path as 'path',
                d.url as 'url',
                d.id_type as 'typeId',
                dt.description_HU as 'type',
                d.id_status as 'statusId',
                ds.description as 'status'
                FROM documents d
                LEFT JOIN users u on u.id = d.created_by
                LEFT JOIN documents_types dt on dt.id = d.id_type
                LEFT JOIN documents_statuses ds on ds.id = d.id_status
                WHERE (:id IS NULL OR d.id = :id)
                AND d.deleted = 0
                order by d.id desc
            ");
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt2 = $this->conn->prepare(
                "SELECT
                ds.id as 'id',
                ds.id_type as 'typeId',
                ds.description as 'name'
                from documents_statuses ds
                WHERE ds.deleted = 0
                ");
            $stmt2->execute();
            $result2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);


            if($result && $result2){
                $response['result'] = $result;
                $response['confirm'] = true;
                $response['documentStatuses'] = $result2;
                echo json_encode($response);
                //print_r(($result));
            }else{
                $errorInfo1 = $stmt->errorInfo();
                $errorInfo2 = $stmt2->errorInfo();
                $error = "Hiba történt az adatok lekérdezése közben" .$errorInfo1[2].$errorInfo2[2];
                echo json_encode($error);
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
            echo json_encode($error);
        }

    }
}

?>