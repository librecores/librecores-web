<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180530072940 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ClassificationHierarchy (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_D13880A5727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ProjectClassification (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, categories LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_64065819166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ClassificationHierarchy ADD CONSTRAINT FK_D13880A5727ACA70 FOREIGN KEY (parent_id) REFERENCES ClassificationHierarchy (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ProjectClassification ADD CONSTRAINT FK_64065819166D1F9C FOREIGN KEY (project_id) REFERENCES Project (id) ON DELETE CASCADE');

        foreach (self::classifier as $categories) {
            $classificationHierarchy = ['parent_id' => $categories[0], 'name' => $categories[1]];
            $this->addSql('INSERT INTO ClassificationHierarchy(parent_id,name) VALUES (:parent_id,:name)', $classificationHierarchy);
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ClassificationHierarchy DROP FOREIGN KEY FK_D13880A5727ACA70');
        $this->addSql('DROP TABLE ClassificationHierarchy');
        $this->addSql('DROP TABLE ProjectClassification');
    }

    /**
     * Data for ClassificationHierarchy object
     *
     * Each row of the array contains two values. First parameter
     * for the parent category id. It is null if the category
     * does not have a parent category and the second one is
     * for the category name.
     *
     * @var array classifiers
     *
     */
    const classifier = [
        1 => [NULL, "License"],
        2 => [1, "Free and Open"],
        3 => [2, "Permissive"],
        4 => [3, "BSD"],
        5 => [3, "MIT"],
        6 => [3, "Apache License"],
        7 => [3, "Solderpad License"],
        8 => [3, "Other"],
        9 => [2, "Weak copyleft"],
        10 => [9, "Mozilla Public License (MPL)"],
        11 => [9, "Solderpad License"],
        12 => [9, "GNU Lesser General Public License v2 (LGPLv2)"],
        13 => [9, "GNU Lesser General Public License v2 or later"],
        14 => [9, "GNU Lesser General Public License v3 (LGPLv3)"],
        15 => [9, "GNU Lesser General Public License v3 or Other"],
        16 => [9, "Other"],
        17 => [2, "Copyleft"],
        18 => [17, "GNU Public License v2 (GPLv2)"],
        19 => [17, "GNU Public License v2 or later (GPLv2+)"],
        20 => [17, "GNU Public License v3 (GPLv3)"],
        21 => [17, "GNU Public License v3 or later (GPLv3+)"],
        22 => [1, "Other/Proprietary License"],
        23 => [1, "Public Domain/CC0"],
        24 => [NULL, "Tool"],
        25 => [24, "Simulation"],
        26 => [25, "Verilator"],
        27 => [25, "Icarus Verilog"],
        28 => [25, "GHDL"],
        29 => [25, "Synopsys VCS"],
        30 => [25, "Mentor ModelSim/Questa"],
        31 => [25, "Cadence Incisive (NCsim)"],
        32 => [25, "Aldec Riviera"],
        33 => [25, "Other"],
        34 => [24, "Synthesis/Implementation"],
        35 => [34, "Synopsys Synplify"],
        36 => [34, "Cadence Genus"],
        37 => [34, "Xilinx Vivado"],
        38 => [34, "Xilinx ISE"],
        39 => [34, "Altera Quartus"],
        40 => [34, "Yosys"],
        41 => [NULL, "Target"],
        42 => [41, "Simulation"],
        43 => [41, "FPGA"],
        44 => [43, "Xilinx"],
        45 => [44, "Spartan 3"],
        46 => [44, "Spartan 6"],
        47 => [44, "7 series"],
        48 => [44, "UltraScale"],
        49 => [44, "Other"],
        50 => [43, "Altera/Intel"],
        51 => [43, "Lattice"],
        52 => [43, "Microsemi"],
        53 => [43, "Other"],
        54 => [41, "ASIC"],
        55 => [NULL, "Proven on"],
        56 => [55, "FPGA"],
        57 => [55, "ASIC"],
        58 => [NULL, "Programming Language"],
        59 => [58, "Verilog"],
        60 => [59, "Verilog 95"],
        61 => [59, "Verilog 2001"],
        62 => [59, "SystemVerilog 2005 (IEEE 1800-2005)"],
        63 => [59, "SystemVerilog 2009 (IEEE 1800-2009)"],
        64 => [59, "SystemVerilog 2012 (IEEE 1800-2012)"],
        65 => [59, "SystemVerilog 2017 (IEEE 1800-2017)"],
        66 => [58, "VHDL"],
        67 => [66, "VHDL 1987/1993/2000/2002 (IEEE 1076-1987/1993/2000/2002)"],
        68 => [66, "VHDL 2008 (IEEE 1076-2008)"],
        69 => [58, "Chisel"],
        70 => [58, "MyHDL"],
        71 => [58, "TL-Verilog"],
        72 => [58, "SystemC"],
        73 => [58, "C"],
        74 => [58, "C++"],
        75 => [58, "Perl"],
        76 => [58, "Python"],
        77 => [58, "Java"],
        78 => [58, "TCL"],
        79 => [58, "Other"],
        80 => [NULL, "Topic"],
        81 => [80, "Hardware"],
        82 => [81, "CPU"],
        83 => [82, "OpenRISC"],
        84 => [82, "RISC-V"],
        85 => [82, "Other"],
        86 => [81, "GPU"],
        87 => [81, "DSP"],
        88 => [81,"I/O"],
        89 => [88, "UART"],
        90 => [88, "USB"],
        91 => [88, "PCI Express (PCIe)"],
        92 => [88, "GPIO"],
        93 => [88, "Ethernet"],
        94 => [81, "Interconnect"],
        95 => [94, "Wishbone"],
        96 => [94, "AXI"],
        97 => [81, "Debug and Monitoing"],
        98 => [81, "Crypto and Hashing"],
        99 => [81, "Other"],
        100 => [80, "Software"],
        101 => [100, "Application"],
        102 => [100, "Library"],
        103 => [NULL, "Support"],
        104 => [103, "Commercially supported"],
        105 => [103, "Community supported"],
        106 => [NULL, "LibreCores"],
        107 => [106, "Featured"]
    ];
}
