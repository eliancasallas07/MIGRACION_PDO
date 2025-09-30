<?php
namespace App\Service;
// Importar clases PDO clase principal y errores
use PDO;
use PDOException;
//Clase principal
class DataMigrator
{
//conexion bases de datos origen y destino 
    private PDO $pdoOrigen;
    private PDO $pdoDestino;
//preparamos los datos y los guardamos en para poderlos usar en los metodos de la clase
    public function __construct(PDO $pdoOrigen, PDO $pdoDestino)
    {
        $this->pdoOrigen = $pdoOrigen;
        $this->pdoDestino = $pdoDestino;
    }
//metodo principal que realiza la migracion
    public function migrate(int $limit = 10, int $offset = 0): int
    {
//Bloque try catch manejo de errores
        try {
//Consulta SQL preparar los datos
            $select = $this->pdoOrigen->prepare("
                SELECT id, descripcion, fecha, duracion, tiempo
                FROM tiempos
                ORDER BY id
                LIMIT :limit OFFSET :offset
            ");
            $select->bindValue(':limit', $limit, PDO::PARAM_INT);
            $select->bindValue(':offset', $offset, PDO::PARAM_INT);
//Obtiene todas las filas de la consulta SQL
            $select->execute();
            $rows = $select->fetchAll(PDO::FETCH_ASSOC);
//Verificacion de consulta
            if (empty($rows)) return 0;
//Preparar consulta para insertar los datos
            $insert = $this->pdoDestino->prepare("
                INSERT INTO tiempos (id_externo, descripcion, fecha, duracion, tiempo)
                VALUES (:id_externo, :descripcion, :fecha, :duracion, :tiempo)
            ");
//Recorrido por cada fila de su tabla origen
            foreach ($rows as $r) {
                $insert->execute([
                    ':id_externo'  => $r['id'],
                    ':descripcion' => $r['descripcion'],
                    ':fecha'       => $r['fecha'],
                    ':duracion'    => $r['duracion'],
                    ':tiempo'      => $r['tiempo'],
                ]);
            }

            return count($rows);
        } catch (PDOException $e) {
            throw new \RuntimeException('Error en migración: ' . $e->getMessage());
        }
    }

//Mostar ultimos regristros,tambien la usamos en DataMigrateCommand 

    public function getLastInsertedRecords(int $limit = 10): array
    {
        $stmt = $this->pdoDestino->prepare("
            SELECT * FROM tiempos
            ORDER BY id DESC
            LIMIT :limit
        ");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
