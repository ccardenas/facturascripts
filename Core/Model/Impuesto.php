<?php
/**
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace FacturaScripts\Core\Model;

/**
 * Un impuesto (IVA) que puede estar asociado a artículos, líneas de albaranes,
 * facturas, etc.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class Impuesto
{
    use Base\ModelTrait;

    /**
     * Clave primaria. varchar(10).
     * @var string
     */
    public $codimpuesto;

    /**
     * Código de la subcuenta para ventas.
     * @var string
     */
    public $codsubcuentarep;

    /**
     * Código de la subcuenta para compras.
     * @var string
     */
    public $codsubcuentasop;
    /**
     * TODO
     * @var string
     */
    public $descripcion;
    /**
     * TODO
     * @var float
     */
    public $iva;
    /**
     * TODO
     * @var float
     */
    public $recargo;

    /**
     * Impuesto constructor.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        $this->init(__CLASS__, 'impuestos', 'codimpuesto');
        if (is_null($data) || empty($data)) {
            $this->clear();
        } else {
            $this->loadFromData($data);
        }
    }

    /**
     * Resetea los valores de todas las propiedades modelo.
     */
    public function clear()
    {
        $this->codimpuesto = null;
        $this->codsubcuentarep = null;
        $this->codsubcuentasop = null;
        $this->descripcion = null;
        $this->iva = 0;
        $this->recargo = 0;
    }

    /**
     * Devuelve la url donde ver/modificar estos datos
     * @return string
     */
    public function url()
    {
        if ($this->codimpuesto === null) {
            return 'index.php?page=ContabilidadImpuestos';
        }
        return 'index.php?page=ContabilidadImpuestos#' . $this->codimpuesto;
    }

    /**
     * Devuelve TRUE si el impuesto es el predeterminado del usuario
     * @return bool
     */
    public function isDefault()
    {
        return ($this->codimpuesto === $this->defaultItems->codImpuesto());
    }

    /**
     * TODO
     *
     * @param float $iva
     *
     * @return bool|Impuesto
     */
    public function getByIva($iva)
    {
        $sql = 'SELECT * FROM ' . $this->tableName() . ' WHERE iva = ' . $this->var2str((float)$iva) . ';';
        $impuesto = $this->dataBase->select($sql);
        if (!empty($impuesto)) {
            return new Impuesto($impuesto[0]);
        }
        return false;
    }

    /**
     * TODO
     * @return bool
     */
    public function test()
    {
        $status = false;

        $this->codimpuesto = trim($this->codimpuesto);
        $this->descripcion = static::noHtml($this->descripcion);

        if (empty($this->codimpuesto) || strlen($this->codimpuesto) > 10) {
            $this->miniLog->alert('Código del impuesto no válido. Debe tener entre 1 y 10 caracteres.');
        } elseif (empty($this->descripcion) || strlen($this->descripcion) > 50) {
            $this->miniLog->alert('Descripción del impuesto no válida.');
        } else {
            $status = true;
        }

        return $status;
    }

    /**
     * Devuelve un array con todos los impuestos
     * @return array
     */
    public function all()
    {
        /// leemos la lista de la caché
        $impuestolist = $this->cache->get('m_impuesto_all');
        if (!$impuestolist) {
            /// si no encontramos la lista en caché, leemos de la base de datos
            $sql = 'SELECT * FROM ' . $this->tableName() . ' ORDER BY iva DESC;';
            $data = $this->dataBase->select($sql);
            if (!empty($data)) {
                foreach ($data as $i) {
                    $impuestolist[] = new Impuesto($i);
                }
            }

            /// guardamos la lista en caché
            $this->cache->set('m_impuesto_all', $impuestolist);
        }

        return $impuestolist;
    }

    /**
     * Esta función es llamada al crear la tabla del modelo. Devuelve el SQL
     * que se ejecutará tras la creación de la tabla. útil para insertar valores
     * por defecto.
     * @return string
     */
    private function install()
    {
        $this->cleanCache();
        return 'INSERT INTO ' . $this->tableName() . ' (codimpuesto,descripcion,iva,recargo) VALUES '
            . "('IVA0','IVA 0%','0','0'),('IVA21','IVA 21%','21','5.2'),"
            . "('IVA10','IVA 10%','10','1.4'),('IVA4','IVA 4%','4','0.5');";
    }

    /**
     * TODO
     */
    private function cleanCache()
    {
        $this->cache->delete('m_impuesto_all');
    }
}
