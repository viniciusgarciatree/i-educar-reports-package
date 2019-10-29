<?php

namespace App\Services\Educacenso\Version2019;

use App\Services\Educacenso\ImportService as GeneralImportService;
use App\Services\Educacenso\RegistroImportInterface;
use Illuminate\Http\UploadedFile;

class ImportService extends GeneralImportService
{
    /**
     * Retorna o ano a que o service se refere
     *
     * @return int
     */
    public function getYear()
    {
        return 2019;
    }

    /**
     * Retorna o nome da escola a partir da string do arquivo de importação
     *
     * @param $school
     * @return string
     */
    public function getSchoolNameByFile($school)
    {
        $columns = explode(self::DELIMITER, $school[0]);

        return $columns[9];
    }

    /**
     * Verifica se o arquivo está de acordo com as regras do ano
     *
     * todo: Implementar validação do arquivo
     * @param UploadedFile $file
     */
    public function validateFile(UploadedFile $file)
    {

    }

    /**
     * Retorna a classe responsável por importar o registro da linha
     *
     * @param $lineId
     * @return RegistroImportInterface
     */
    public function getRegistroById($lineId)
    {
        $arrayRegistros = [
            '00' => Registro00Import::class,
            '10' => Registro10Import::class,
        ];

        return new $arrayRegistros[$lineId];
    }
}
