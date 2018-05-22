<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180522113941 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


//        $this->addSql('INSERT INTO ClassificationHierarchy(parent_id,name,created_at,updated_at) VALUES (null,"License","2018-05-22 12:22:05","2018-05-22 12:22:05")');

        foreach (self::classifier as $categories)
        {
            $date = new \DateTime;
            $dateTime = $date->format('Y-m-d H:i:s');
            $this->addSql('INSERT INTO ClassificationHierarchy(parent_id,name,created_at,updated_at) VALUES ('.$categories[0].',"'.$categories[1].'","'.$dateTime.'","'.$dateTime.'")');
        }

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('TRUNCATE ClassificationHierarchy');
    }
    /**
    +     * Data for ClassificationHierarchy object
    +     *
    +     * Each row of the array contains two values. First parameter
    +     * for the parent category id. It is null if the category
    +     * does not have a parent category and the second one is
    +     * for the category name.
    +     *
    +     * @var array classifiers
    +     *
    +     */
    const classifier = [
        ['null', "License"],
        [1, "Free and Open"],
        [2, "Permissive"],
        [3, "BSD"],
        [3, "MIT"],
        [3, "Apache License"],
        [3, "Solderpad License"],
        [3, "other"],
        [2, "week copyleft"],
        [9, "Mozilla Public License (MPL)"],
        [9, "Solderpad License"],
        [9, "GNU Lesser General Public License v2 (LGPLv2)"],
        [9, "GNU Lesser General Public License v2 or later"],
        [9, "GNU Lesser General Public License v3 (LGPLv3)"],
        [9, "GNU Lesser General Public License v3 or other"],
        [9, "other"],
        [2, "copyleft"],
        [17, "GNU Public License v2 (GPLv2)"],
        [17, "GNU Public License v2 or later (GPLv2+)"],
        [17, "GNU Public License v3 (GPLv3)"],
        [17, "GNU Public License v3 or later (GPLv3+)"],
        [1, "Other/Proprietary License"],
        [1, "Public Domain/CC0"],
        ['null', "Tool"],
        [24, "Simulation"],
        [25, "Verilator"],
        [25, "Icarus Verilog"],
        [25, "GHDL"],
        [25, "Synopsys VCS"],
        [25, "Mentor ModelSim/Questa"],
        [25, "Cadence Incisive (NCsim)"],
        [25, "Aldec Riviera"],
        [25, "other"],
        [24, "Synthesis/Implementation"],
        [34, "Synopsys Synplify"],
        [34, "Cadence Genus"],
        [34, "Xilinx Vivado"],
        [34, "Xilinx ISE"],
        [34, "Altera Quartus"],
        [34, "Yosys"],
        ['null', "Target"],
        [41, "Simulation"],
        [41, "FPGA"],
        [43, "Xilinx"],
        [44, "Spartan 3"],
        [44, "Spartan 6"],
        [44, "7 series"],
        [44, "UltraScale"],
        [44, "other"],
        [43, "Altera/Intel"],
        [43, "Lattice"],
        [43, "Microsemi"],
        [43, "other"],
        [41, "ASIC"],
        ['null', "Proven on"],
        [55, "FPGA"],
        [55, "ASIC"],
        ['null', "Programming Language"],
        [58, "Verilog"],
        [59, "Verilog 95"],
        [59, "Verilog 2001"],
        [59, "SystemVerilog 2005 (IEEE 1800-2005)"],
        [59, "SystemVerilog 2009 (IEEE 1800-2009)"],
        [59, "SystemVerilog 2012 (IEEE 1800-2012)"],
        [59, "SystemVerilog 2017 (IEEE 1800-2017)"],
        [58, "VHDL"],
        [66, "VHDL 1987/1993/2000/2002 (IEEE 1076-1987/1993/2000/2002)"],
        [66, "VHDL 2008 (IEEE 1076-2008)"],
        [58, "Chisel"],
        [58, "MyHDL"],
        [58, "TL-Verilog"],
        [58, "SystemC"],
        [58, "C"],
        [58, "C++"],
        [58, "Perl"],
        [58, "Python"],
        [58, "Java"],
        [58, "TCL"],
        [58, "other"],
        ['null', "Topic"],
        [80, "Hardware"],
        [81, "CPU"],
        [82, "OpenRISC"],
        [82, "RISC-V"],
        [82, "other"],
        [81, "GPU"],
        [81, "DSP"],
        [81,"I/O"],
        [88, "UART"],
        [88, "USB"],
        [88, "PCI Express (PCIe)"],
        [88, "GPIO"],
        [88, "Ethernet"],
        [81, "Interconnect"],
        [94, "Wishbone"],
        [94, "AXI"],
        [81, "Debug and Monitoing"],
        [81, "Crypto and Hashing"],
        [81, "other"],
        [80, "Software"],
        [100, "Application"],
        [100, "Library"],
        ['null', "Support"],
        [103, "Commercially supported"],
        [103, "Community supported"],
        ['null', "LibreCores"],
        [106, "Featured"]
    ];
}
