-- 22/04/25 --
ALTER TABLE `plant` ADD `misc` VARCHAR(5) NOT NULL DEFAULT '1' AFTER `locals`;

INSERT INTO `status` (`id`, `status`, `prefix`, `misc_id`, `deleted`) VALUES (NULL, 'Misc', 'M', '4', '0');

-- 23/04/25 --
ALTER TABLE `weight` ADD `seal_no` VARCHAR(50) NULL AFTER `invoice_no`;

-- 24/04/25 --
CREATE TABLE `Weight_Container` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `transaction_status` varchar(100) NOT NULL,
  `weight_type` varchar(100) NOT NULL,
  `customer_type` varchar(100) DEFAULT NULL,
  `transaction_date` datetime NOT NULL,
  `lorry_plate_no1` varchar(100) DEFAULT NULL,
  `lorry_plate_no2` varchar(100) DEFAULT NULL,
  `supplier_weight` varchar(100) DEFAULT NULL,
  `order_weight` varchar(100) DEFAULT NULL,
  `plant_code` varchar(50) DEFAULT NULL,
  `plant_name` varchar(50) DEFAULT NULL,
  `site_code` varchar(50) DEFAULT NULL,
  `site_name` varchar(100) DEFAULT NULL,
  `agent_code` varchar(50) DEFAULT NULL,
  `agent_name` varchar(50) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(50) DEFAULT NULL,
  `supplier_code` varchar(50) DEFAULT NULL,
  `supplier_name` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(50) DEFAULT NULL,
  `product_description` varchar(150) DEFAULT NULL,
  `ex_del` varchar(5) DEFAULT 'EX',
  `raw_mat_code` varchar(50) DEFAULT NULL,
  `raw_mat_name` varchar(100) DEFAULT NULL,
  `container_no` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `seal_no` varchar(50) DEFAULT NULL,
  `purchase_order` varchar(50) DEFAULT NULL,
  `delivery_no` varchar(50) DEFAULT NULL,
  `transporter_code` varchar(50) DEFAULT NULL,
  `transporter` varchar(50) DEFAULT NULL,
  `destination_code` varchar(50) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `gross_weight1` varchar(100) NOT NULL,
  `gross_weight1_date` datetime NOT NULL,
  `tare_weight1` varchar(100) DEFAULT NULL,
  `tare_weight1_date` datetime DEFAULT NULL,
  `nett_weight1` varchar(100) NOT NULL,
  `gross_weight2` varchar(100) DEFAULT NULL,
  `gross_weight2_date` datetime DEFAULT NULL,
  `tare_weight2` varchar(100) DEFAULT NULL,
  `tare_weight2_date` datetime DEFAULT NULL,
  `nett_weight2` varchar(100) DEFAULT NULL,
  `reduce_weight` varchar(100) NOT NULL,
  `final_weight` varchar(150) DEFAULT NULL,
  `weight_different` varchar(100) DEFAULT NULL,
  `is_complete` varchar(100) NOT NULL DEFAULT 'N',
  `is_cancel` varchar(100) NOT NULL DEFAULT 'N',
  `is_approved` varchar(3) NOT NULL DEFAULT 'Y',
  `manual_weight` varchar(100) NOT NULL,
  `indicator_id` varchar(100) NOT NULL,
  `weighbridge_id` varchar(100) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` varchar(50) NOT NULL,
  `indicator_id_2` varchar(50) DEFAULT NULL,
  `unit_price` varchar(10) DEFAULT NULL,
  `sub_total` varchar(10) NOT NULL DEFAULT '0.00',
  `sst` varchar(10) NOT NULL DEFAULT '0.00',
  `total_price` varchar(10) NOT NULL DEFAULT '0.00',
  `load_drum` varchar(4) DEFAULT NULL,
  `no_of_drum` int(100) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 0,
  `approved_by` int(5) DEFAULT NULL,
  `approved_reason` text DEFAULT NULL,
  `cancelled_reason` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Weight_Container` ADD PRIMARY KEY (`id`);

ALTER TABLE `Weight_Container` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

DELIMITER $$
CREATE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW INSERT INTO Weight_Container_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, 
        NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

CREATE TABLE `Weight_Container_Log` (
  `id` int(11) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `transaction_status` varchar(100) DEFAULT NULL,
  `weight_type` varchar(100) DEFAULT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `lorry_plate_no1` varchar(100) DEFAULT NULL,
  `lorry_plate_no2` varchar(100) DEFAULT NULL,
  `supplier_weight` varchar(100) DEFAULT NULL,
  `order_weight` varchar(100) DEFAULT NULL,
  `plant_code` varchar(50) DEFAULT NULL,
  `plant_name` varchar(50) DEFAULT NULL,
  `site_code` varchar(50) DEFAULT NULL,
  `site_name` varchar(100) DEFAULT NULL,
  `agent_code` varchar(50) DEFAULT NULL,
  `agent_name` varchar(50) DEFAULT NULL,
  `customer_code` varchar(50) DEFAULT NULL,
  `customer_name` varchar(50) DEFAULT NULL,
  `supplier_code` varchar(50) DEFAULT NULL,
  `supplier_name` varchar(50) DEFAULT NULL,
  `product_code` varchar(50) DEFAULT NULL,
  `product_name` varchar(50) DEFAULT NULL,
  `product_description` varchar(150) DEFAULT NULL,
  `ex_del` varchar(5) DEFAULT NULL,
  `raw_mat_code` varchar(50) DEFAULT NULL,
  `raw_mat_name` varchar(100) DEFAULT NULL,
  `container_no` varchar(50) DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `purchase_order` varchar(50) DEFAULT NULL,
  `delivery_no` varchar(50) DEFAULT NULL,
  `transporter_code` varchar(50) DEFAULT NULL,
  `transporter` varchar(50) DEFAULT NULL,
  `destination_code` varchar(50) DEFAULT NULL,
  `destination` varchar(100) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `gross_weight1` varchar(100) DEFAULT NULL,
  `gross_weight1_date` datetime DEFAULT NULL,
  `tare_weight1` varchar(100) DEFAULT NULL,
  `tare_weight1_date` datetime DEFAULT NULL,
  `nett_weight1` varchar(100) DEFAULT NULL,
  `gross_weight2` varchar(100) DEFAULT NULL,
  `gross_weight2_date` datetime DEFAULT NULL,
  `tare_weight2` varchar(100) DEFAULT NULL,
  `tare_weight2_date` datetime DEFAULT NULL,
  `nett_weight2` varchar(100) DEFAULT NULL,
  `reduce_weight` varchar(100) DEFAULT NULL,
  `final_weight` varchar(150) DEFAULT NULL,
  `weight_different` varchar(100) DEFAULT NULL,
  `is_complete` varchar(100) DEFAULT NULL,
  `is_cancel` varchar(100) DEFAULT NULL,
  `is_approved` varchar(3) DEFAULT NULL,
  `manual_weight` varchar(100) DEFAULT NULL,
  `indicator_id` varchar(100) DEFAULT NULL,
  `weighbridge_id` varchar(100) DEFAULT NULL,
  `indicator_id_2` varchar(50) DEFAULT NULL,
  `unit_price` varchar(10) DEFAULT NULL,
  `sub_total` varchar(10) DEFAULT NULL,
  `sst` varchar(10) DEFAULT NULL,
  `total_price` varchar(10) DEFAULT NULL,
  `load_drum` varchar(4) DEFAULT NULL,
  `no_of_drum` int(100) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `approved_by` int(5) DEFAULT NULL,
  `approved_reason` text DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Weight_Container_Log` ADD PRIMARY KEY (`id`);

ALTER TABLE `Weight_Container_Log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 03/05/2025 --
UPDATE status SET `prefix` = 'D' WHERE status = 'Sales';

UPDATE status SET `prefix` = 'R' WHERE status = 'Purchase';

UPDATE status SET `prefix` = 'I' WHERE status = 'Local';

UPDATE status SET `prefix` = 'M' WHERE status = 'Misc';

ALTER TABLE `Weight` ADD `container_no2` VARCHAR(50) NULL AFTER `seal_no`, ADD `seal_no2` VARCHAR(50) NULL AFTER `container_no2`;

ALTER TABLE `Weight_Container` ADD `container_no2` VARCHAR(50) NULL AFTER `seal_no`, ADD `seal_no2` VARCHAR(50) NULL AFTER `container_no2`;

ALTER TABLE `Weight` ADD `gross_weight_by1` VARCHAR(50) NULL AFTER `gross_weight1_date`;

ALTER TABLE `Weight` ADD `tare_weight_by1` VARCHAR(50) NULL AFTER `tare_weight1_date`;

ALTER TABLE `Weight` ADD `gross_weight_by2` VARCHAR(50) NULL AFTER `gross_weight2_date`;

ALTER TABLE `Weight` ADD `tare_weight_by2` VARCHAR(50) NULL AFTER `tare_weight2_date`;

ALTER TABLE `Weight_Container` ADD `gross_weight_by1` VARCHAR(50) NULL AFTER `gross_weight1_date`;

ALTER TABLE `Weight_Container` ADD `tare_weight_by1` VARCHAR(50) NULL AFTER `tare_weight1_date`;

ALTER TABLE `Weight_Container` ADD `gross_weight_by2` VARCHAR(50) NULL AFTER `gross_weight2_date`;

ALTER TABLE `Weight_Container` ADD `tare_weight_by2` VARCHAR(50) NULL AFTER `tare_weight2_date`;

-- 14/05/2025 --
ALTER TABLE `Customer` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Customer` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Customer_Log` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Customer_Log` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Supplier` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Supplier` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Supplier_Log` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Supplier_Log` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Transporter` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Transporter` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Transporter_Log` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Transporter_Log` ADD `contact_name` VARCHAR(100) NULL AFTER `fax_no`, ADD `ic_no` VARCHAR(100) NULL AFTER `contact_name`, ADD `tin_no` VARCHAR(100) NULL AFTER `ic_no`;

ALTER TABLE `Company` CHANGE `created_date` `created_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `Company` CHANGE `modified_date` `modified_date` DATETIME on update CURRENT_TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE `Company` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Company` ADD `tin_no` VARCHAR(100) NULL AFTER `fax_no`, ADD `mobile_no` VARCHAR(50) NULL AFTER `tin_no`;

ALTER TABLE `Company_Log` DROP COLUMN `created_date`;

ALTER TABLE `Company_Log` DROP COLUMN `created_by`;

ALTER TABLE `Company_Log` DROP COLUMN `modified_date`;

ALTER TABLE `Company_Log` DROP COLUMN `modified_by`;

ALTER TABLE `Company_Log` ADD `new_reg_no` VARCHAR(100) NULL AFTER `company_reg_no`;

ALTER TABLE `Company_Log` ADD `tin_no` VARCHAR(100) NULL AFTER `fax_no`, ADD `mobile_no` VARCHAR(50) NULL AFTER `tin_no`;

-- 14/06/2025 --
ALTER TABLE `Vehicle` ADD `supplier_code` VARCHAR(50) NOT NULL AFTER `customer_name`, ADD `supplier_name` VARCHAR(100) NOT NULL AFTER `supplier_code`;

ALTER TABLE `Vehicle_Log` ADD `supplier_code` VARCHAR(50) NOT NULL AFTER `customer_name`, ADD `supplier_name` VARCHAR(100) NOT NULL AFTER `supplier_code`;

-- 15/06/2025 --
ALTER TABLE `Weight_Container` ADD `lorry_no2_weight` VARCHAR(100) NULL AFTER `nett_weight1`, ADD `empty_container2_weight` VARCHAR(100) NULL AFTER `lorry_no2_weight`;

ALTER TABLE `Weight_Container_Log` ADD `lorry_no2_weight` VARCHAR(100) NULL AFTER `nett_weight1`, ADD `empty_container2_weight` VARCHAR(100) NULL AFTER `lorry_no2_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW INSERT INTO Weight_Container_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, 
        NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight` ADD `lorry_no2_weight` VARCHAR(100) NULL AFTER `nett_weight1`, ADD `empty_container2_weight` VARCHAR(100) NULL AFTER `lorry_no2_weight`;

ALTER TABLE `Weight_Log` ADD `lorry_no2_weight` VARCHAR(100) NULL AFTER `nett_weight1`, ADD `empty_container2_weight` VARCHAR(100) NULL AFTER `lorry_no2_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, 
        NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Vehicle` CHANGE `supplier_code` `supplier_code` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Vehicle` CHANGE `supplier_name` `supplier_name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Vehicle_Log` CHANGE `supplier_code` `supplier_code` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Vehicle_Log` CHANGE `supplier_name` `supplier_name` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Weight` CHANGE `gross_weight1` `gross_weight1` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Weight` CHANGE `gross_weight1_date` `gross_weight1_date` DATETIME NULL;

ALTER TABLE `Weight_Log` CHANGE `gross_weight1` `gross_weight1` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `Weight_Log` CHANGE `gross_weight1_date` `gross_weight1_date` DATETIME NULL;

ALTER TABLE `Weight` ADD `replacement_container` VARCHAR(100) NULL AFTER `empty_container2_weight`;

ALTER TABLE `Weight_Log` ADD `replacement_container` VARCHAR(100) NULL AFTER `empty_container2_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, 
        NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` ADD `replacement_container` VARCHAR(100) NULL AFTER `empty_container2_weight`;

ALTER TABLE `Weight_Container_Log` ADD `replacement_container` VARCHAR(100) NULL AFTER `empty_container2_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW INSERT INTO Weight_Container_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container,
        NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, 
        NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 22/06/2025 --
CREATE TABLE `weight_product` (
  `id` int(11) NOT NULL,
  `weight_id` int(11) DEFAULT NULL,
  `product` varchar(100) DEFAULT NULL,
  `product_packing` varchar(100) DEFAULT NULL,
  `product_gross` varchar(100) DEFAULT NULL,
  `product_tare` varchar(100) DEFAULT NULL,
  `product_nett` varchar(100) DEFAULT NULL,
  `status` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `Weight_Product` ADD PRIMARY KEY (`id`);

ALTER TABLE `Weight_Product` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- 25/06/2025 --
ALTER TABLE `Plant_Log` ADD `misc` VARCHAR(5) NULL AFTER `locals`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PLANT` AFTER INSERT ON `Plant` FOR EACH ROW INSERT INTO Plant_Log (
    plant_id, plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, sales, purchase, locals, misc, do_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.plant_code, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.sales, NEW.purchase, NEW.locals, NEW.misc, NEW.do_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PLANT` BEFORE UPDATE ON `Plant` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Plant_Log table
    INSERT INTO Plant_Log (
        plant_id, plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, sales, purchase, locals, misc, do_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.plant_code, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.sales, NEW.purchase, NEW.locals, NEW.misc, NEW.do_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 27/06/2025 --
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_CUSTOMER` AFTER INSERT ON `Customer` FOR EACH ROW 
INSERT INTO Customer_Log (
    customer_id, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, address_line_4, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.customer_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.address_line_4, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_CUSTOMER` BEFORE UPDATE ON `Customer` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Customer_Log table
    INSERT INTO Customer_Log (
        customer_id, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, address_line_4, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.customer_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.address_line_4, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_DESTINATION` AFTER INSERT ON `Destination` FOR EACH ROW 
INSERT INTO Destination_Log (
    destination_id, destination_code, name, description, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.destination_code, NEW.name, NEW.description, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_DESTINATION` BEFORE UPDATE ON `Destination` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Destination_Log table
    INSERT INTO Destination_Log (
        destination_id, destination_code, name, description, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.destination_code, NEW.name, NEW.description, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PRODUCT` AFTER INSERT ON `Product` FOR EACH ROW 
INSERT INTO Product_Log (
    product_id, product_code, name, price, description, variance, high, low, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.product_code, NEW.name, NEW.price, NEW.description, NEW.variance, NEW.high, NEW.low, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PRODUCT` BEFORE UPDATE ON `Product` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Product_Log table
    INSERT INTO Product_Log (
    product_id, product_code, name, price, description, variance, high, low, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.product_code, NEW.name, NEW.price, NEW.description, NEW.variance, NEW.high, NEW.low, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_RAW_MAT` AFTER INSERT ON `Raw_Mat` FOR EACH ROW 
INSERT INTO Raw_Mat_Log (
    raw_mat_id, raw_mat_code, name, price, description, variance, high, low, type, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.raw_mat_code, NEW.name, NEW.price, NEW.description, NEW.variance, NEW.high, NEW.low, NEW.type, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_RAW_MAT` BEFORE UPDATE ON `Raw_Mat` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Raw_Mat_Log table
    INSERT INTO Raw_Mat_Log (
        raw_mat_id, raw_mat_code, name, price, description, variance, high, low, type, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.raw_mat_code, NEW.name, NEW.price, NEW.description, NEW.variance, NEW.high, NEW.low, NEW.type, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SUPPLIER` AFTER INSERT ON `Supplier` FOR EACH ROW 
INSERT INTO Supplier_Log (
    supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SUPPLIER` BEFORE UPDATE ON `Supplier` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Supplier_Log table
    INSERT INTO Supplier_Log (
        supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_VEH` AFTER INSERT ON `Vehicle` FOR EACH ROW 
INSERT INTO Vehicle_Log (
    vehicle_id, veh_number, vehicle_weight, transporter_code, transporter_name, ex_del, customer_code, customer_name, supplier_code, supplier_name, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.veh_number, NEW.vehicle_weight, NEW.transporter_code, NEW.transporter_name, NEW.ex_del, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_VEH` BEFORE UPDATE ON `Vehicle` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Vehicle_Log table
    INSERT INTO Vehicle_Log (
        vehicle_id, veh_number, vehicle_weight, transporter_code, transporter_name, ex_del, customer_code, customer_name, supplier_code, supplier_name, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.veh_number, NEW.vehicle_weight, NEW.transporter_code, NEW.transporter_name, NEW.ex_del, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_TRANSPORTER` AFTER INSERT ON `Transporter` FOR EACH ROW 
INSERT INTO Transporter_Log (
    transporter_id, transporter_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.transporter_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_TRANSPORTER` BEFORE UPDATE ON `Transporter` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Transporter_Log table
    INSERT INTO Transporter_Log (
        transporter_id, transporter_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.transporter_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_USER` AFTER INSERT ON `Users` FOR EACH ROW 
INSERT INTO Users_Log (
    user_id, employee_code, username, name, useremail, password, plant_id, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.employee_code, NEW.username, NEW.name, NEW.useremail, NEW.password, NEW.plant_id, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_USER` BEFORE UPDATE ON `Users` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Users_Log table
    INSERT INTO Users_Log (
        user_id, employee_code, username, name, useremail, password, plant_id, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.employee_code, NEW.username, NEW.name, NEW.useremail, NEW.password, NEW.plant_id, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 19/10/25--
ALTER TABLE `Weight` ADD `weight_different_perc` VARCHAR(50) NULL AFTER `weight_different`;

ALTER TABLE `Weight_Log` ADD `weight_different_perc` VARCHAR(50) NULL AFTER `weight_different`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` ADD `weight_different_perc` VARCHAR(50) NULL AFTER `reduce_weight`;

ALTER TABLE `Weight_Container_Log` ADD `weight_different_perc` VARCHAR(50) NULL AFTER `reduce_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW INSERT INTO Weight_Container_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, site_code, site_name, agent_code, agent_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, ex_del, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, load_drum, no_of_drum, status, approved_by, approved_reason, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name, NEW.site_code, NEW.site_name, 
        NEW.agent_code, NEW.agent_name, NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.ex_del, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container,
        NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, 
        NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.load_drum, 
        NEW.no_of_drum, NEW.status, NEW.approved_by, NEW.approved_reason, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 20/05/2026 --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('clear_all_code', 'Clear All', '清除全部', 'Kosongkan Semua', 'அனைத்தையும் அழி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('cancelled_code', 'Cancelled', '已取消', 'Dibatalkan', 'ரத்து செய்யப்பட்டது');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('address_line_code', 'Address Line', '地址行', 'Baris Alamat', 'முகவரி வரி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_reg_no_code', 'Company Reg No.', '公司注册号', 'No. Pendaftaran Syarikat', 'நிறுவன பதிவு எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_name_code', 'Company Name', '公司名称', 'Nama Syarikat', 'நிறுவன பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_address_code', 'Company Address', '公司地址', 'Alamat Syarikat', 'நிறுவன முகவரி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_phone_code', 'Company Phone', '公司电话', 'Telefon Syarikat', 'நிறுவன தொலைபேசி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_fax_code', 'Company Fax', '公司传真', 'Faks Syarikat', 'நிறுவன தொலைநகல்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('update_code', 'Update', '更新', 'Kemaskini', 'புதுப்பி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('indicator_code', 'Indicator', '指示器', 'Penunjuk', 'காட்டி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('serial_port_code', 'Serial Port', '串行端口', 'Port Bersiri', 'தொடர் துறை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('baud_rate_code', 'Baud Rate', '波特率', 'Kadar Baud', 'பாட் வீதம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('data_bits_code', 'Data Bits', '数据位', 'Bit Data', 'தரவு பிட்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('parity_code', 'Parity', '奇偶校验', 'Pariti', 'சமநிலை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('stop_bits_code', 'Stop Bits', '停止位', 'Bit Henti', 'நிறுத்த பிட்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('data_category_code', 'Data Category', '数据类别', 'Kategori Data', 'தரவு வகை');
UPDATE `message_resource` SET `en`='ID No', `zh`='身份证号码', `my`='No. Kad Pengenalan', `ne`='அடையாள எண்' WHERE `message_key_code`='ic_code';

-- 01/06/2026 --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('error_log_code', 'Error Log', '错误日志', 'Log Ralat', 'பிழை பதிவு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('please_fill_in_the_field_code', 'Please fill in the field.', '请填写此字段。', 'Sila isi medan ini.', 'இந்த புலத்தை நிரப்பவும்.');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('message_code_code', 'Message Code', '消息代码', 'Kod Mesej', 'செய்தி குறியீடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('pending_bin_code', 'Pending Bin', '待处理箱', 'Tong Menunggu', 'நிலுவையில் உள்ள பெட்டி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('new_empty_bin_code', 'New Empty Bin', '新空箱', 'Tong Kosong Baru', 'புதிய காலி பெட்டி');

DROP TABLE IF EXISTS `Product_RawMat`;
DROP TABLE IF EXISTS `Bitumen`;
DROP TABLE IF EXISTS `Agents`;
DROP TABLE IF EXISTS `Agents_Log`;
DROP TABLE IF EXISTS `Inventory`;
DROP TABLE IF EXISTS `Site`;
DROP TABLE IF EXISTS `Site_Log`;
DROP TABLE IF EXISTS `Unit`;
DROP TABLE IF EXISTS `Unit_Log`;
DROP TABLE IF EXISTS `Weight_Product`;

ALTER TABLE `Product` DROP `price`;
ALTER TABLE `Product_Log` DROP `price`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PRODUCT` AFTER INSERT ON `Product` FOR EACH ROW 
INSERT INTO Product_Log (
    product_id, product_code, name, description, variance, high, low, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.product_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PRODUCT` BEFORE UPDATE ON `Product` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Product_Log table
    INSERT INTO Product_Log (
    product_id, product_code, name, description, variance, high, low, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.product_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Raw_Mat` DROP `price`;
ALTER TABLE `Raw_Mat` DROP `type`;
ALTER TABLE `Raw_Mat_Log` DROP `price`;
ALTER TABLE `Raw_Mat_Log` DROP `type`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_RAW_MAT` AFTER INSERT ON `Raw_Mat` FOR EACH ROW 
INSERT INTO Raw_Mat_Log (
    raw_mat_id, raw_mat_code, name, description, variance, high, low, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.raw_mat_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_RAW_MAT` BEFORE UPDATE ON `Raw_Mat` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Raw_Mat_Log table
    INSERT INTO Raw_Mat_Log (
        raw_mat_id, raw_mat_code, name, description, variance, high, low, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.raw_mat_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Vehicle` DROP `ex_del`;
ALTER TABLE `Vehicle_Log` DROP `ex_del`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_VEH` AFTER INSERT ON `Vehicle` FOR EACH ROW 
INSERT INTO Vehicle_Log (
    vehicle_id, veh_number, vehicle_weight, transporter_code, transporter_name, customer_code, customer_name, supplier_code, supplier_name, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.veh_number, NEW.vehicle_weight, NEW.transporter_code, NEW.transporter_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_VEH` BEFORE UPDATE ON `Vehicle` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Vehicle_Log table
    INSERT INTO Vehicle_Log (
        vehicle_id, veh_number, vehicle_weight, transporter_code, transporter_name, customer_code, customer_name, supplier_code, supplier_name, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.veh_number, NEW.vehicle_weight, NEW.transporter_code, NEW.transporter_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight` DROP `agent_code`;
ALTER TABLE `Weight` DROP `agent_name`;
ALTER TABLE `Weight_Log` DROP `agent_code`;
ALTER TABLE `Weight_Log` DROP `agent_name`;
ALTER TABLE `Weight` DROP `site_code`;
ALTER TABLE `Weight` DROP `site_name`;
ALTER TABLE `Weight_Log` DROP `site_code`;
ALTER TABLE `Weight_Log` DROP `site_name`;
ALTER TABLE `Weight` DROP `ex_del`;
ALTER TABLE `Weight_Log` DROP `ex_del`;
ALTER TABLE `Weight` DROP `load_drum`;
ALTER TABLE `Weight_Log` DROP `load_drum`;
ALTER TABLE `Weight` DROP `no_of_drum`;
ALTER TABLE `Weight_Log` DROP `no_of_drum`;
ALTER TABLE `Weight` DROP `approved_by`;
ALTER TABLE `Weight_Log` DROP `approved_by`;
ALTER TABLE `Weight` DROP `approved_reason`;
ALTER TABLE `Weight_Log` DROP `approved_reason`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` DROP `agent_code`;
ALTER TABLE `Weight_Container` DROP `agent_name`;
ALTER TABLE `Weight_Container_Log` DROP `agent_code`;
ALTER TABLE `Weight_Container_Log` DROP `agent_name`;
ALTER TABLE `Weight_Container` DROP `site_code`;
ALTER TABLE `Weight_Container` DROP `site_name`;
ALTER TABLE `Weight_Container_Log` DROP `site_code`;
ALTER TABLE `Weight_Container_Log` DROP `site_name`;
ALTER TABLE `Weight_Container` DROP `ex_del`;
ALTER TABLE `Weight_Container_Log` DROP `ex_del`;
ALTER TABLE `Weight_Container` DROP `load_drum`;
ALTER TABLE `Weight_Container_Log` DROP `load_drum`;
ALTER TABLE `Weight_Container` DROP `no_of_drum`;
ALTER TABLE `Weight_Container_Log` DROP `no_of_drum`;
ALTER TABLE `Weight_Container` DROP `approved_by`;
ALTER TABLE `Weight_Container_Log` DROP `approved_by`;
ALTER TABLE `Weight_Container` DROP `approved_reason`;
ALTER TABLE `Weight_Container_Log` DROP `approved_reason`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW 
INSERT INTO Weight_Container_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 05/09/2026 --
ALTER TABLE `Weight` ADD `customer_side_company` VARCHAR(100) NULL AFTER `status`, ADD `customer_side_removal_pass_no` VARCHAR(100) NULL AFTER `customer_side_company`, ADD `customer_side_license_no` VARCHAR(100) NULL AFTER `customer_side_removal_pass_no`, ADD `customer_side_moisture_content` VARCHAR(100) NULL AFTER `customer_side_license_no`, ADD `customer_side_officer_name` VARCHAR(100) NULL AFTER `customer_side_moisture_content`, ADD `customer_side_rainbow_driver` VARCHAR(100) NULL AFTER `customer_side_officer_name`, ADD `customer_side_time_in` DATETIME NULL AFTER `customer_side_rainbow_driver`, ADD `customer_side_time_out` DATETIME NULL AFTER `customer_side_time_in`, ADD `cust_side_do_no` VARCHAR(100) NULL AFTER `customer_side_time_out`, ADD `cust_side_mc` VARCHAR(100) NULL AFTER `cust_side_do_no`, ADD `cust_side_first_weight` VARCHAR(100) NULL AFTER `cust_side_mc`, ADD `cust_side_second_weight` VARCHAR(100) NULL AFTER `cust_side_first_weight`, ADD `cust_side_nett_weight` VARCHAR(100) NULL AFTER `cust_side_second_weight`, ADD `weight_difference` VARCHAR(100) NULL AFTER `cust_side_nett_weight`;

ALTER TABLE `Weight_Log` ADD `customer_side_company` VARCHAR(100) NULL AFTER `status`, ADD `customer_side_removal_pass_no` VARCHAR(100) NULL AFTER `customer_side_company`, ADD `customer_side_license_no` VARCHAR(100) NULL AFTER `customer_side_removal_pass_no`, ADD `customer_side_moisture_content` VARCHAR(100) NULL AFTER `customer_side_license_no`, ADD `customer_side_officer_name` VARCHAR(100) NULL AFTER `customer_side_moisture_content`, ADD `customer_side_rainbow_driver` VARCHAR(100) NULL AFTER `customer_side_officer_name`, ADD `customer_side_time_in` DATETIME NULL AFTER `customer_side_rainbow_driver`, ADD `customer_side_time_out` DATETIME NULL AFTER `customer_side_time_in`, ADD `cust_side_do_no` VARCHAR(100) NULL AFTER `customer_side_time_out`, ADD `cust_side_mc` VARCHAR(100) NULL AFTER `cust_side_do_no`, ADD `cust_side_first_weight` VARCHAR(100) NULL AFTER `cust_side_mc`, ADD `cust_side_second_weight` VARCHAR(100) NULL AFTER `cust_side_first_weight`, ADD `cust_side_nett_weight` VARCHAR(100) NULL AFTER `cust_side_second_weight`, ADD `weight_difference` VARCHAR(100) NULL AFTER `cust_side_nett_weight`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
) 
VALUES (
    NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status,
        NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference,
        action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Customer` ADD `email` VARCHAR(100) NULL AFTER `tin_no`;
ALTER TABLE `Customer_Log` ADD `email` VARCHAR(100) NULL AFTER `tin_no`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_CUSTOMER` AFTER INSERT ON `Customer` FOR EACH ROW 
INSERT INTO Customer_Log (
    customer_id, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, address_line_4, phone_no, fax_no, contact_name, ic_no, tin_no, email, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.customer_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.address_line_4, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.email, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_CUSTOMER` BEFORE UPDATE ON `Customer` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Customer_Log table
    INSERT INTO Customer_Log (
        customer_id, customer_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, address_line_4, phone_no, fax_no, contact_name, ic_no, tin_no, email, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.customer_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.address_line_4, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.email, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Company` ADD `email` VARCHAR(100) NULL AFTER `tin_no`;
ALTER TABLE `Company_Log` ADD `email` VARCHAR(100) NULL AFTER `tin_no`;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('reduce_weight_code', 'Wastage', '损耗', 'Pembaziran', 'சேதாரம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('sales_code', "Sales", "销售", "Jualan", "விற்பனை");
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('purchase_code', "Purchase", "购买", "Pembelian", "வாங்குதல்");
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('internal_transfer_code', "Transfer to Port", "转运至港口", "Pemindahan ke Pelabuhan", "துறைமுகத்திற்கு இடமாற்றம்");
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('date_code', 'Date', '日期', 'Tarikh', 'தேதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('trans_type_code', 'Trans. Type', '交通工具类型', 'Jenis pengangkutan', 'போக்குவரத்து வகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('driver_code', 'Driver', '司机', 'Pemandu', 'ஓட்டுனர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('weighing_by_code', 'Weighing By', '称重负责人', 'Ditimbang oleh', 'பொறுப்பாளர் மூலம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('name_driver_code', 'Name of Driver', '司机姓名', 'Nama Pemandu', 'ஓட்டுநரின் பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('ic_no_of_driver_code', 'I/C No. of Driver', '司机身份证号码', 'No. K/P pemandu', 'ஓட்டுநரின் அடையாள அட்டை எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('type_of_collection_code', 'Type of Collection', '收集类型', 'Jenis kutipan', 'சேகரிப்பு வகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('collection_location_code', 'Collection Location', '收集地点', 'Lokasi Kutipan', 'சேகரிப்பு இடம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('signature_of_driver_code', 'Signature of Driver', '司机签名', 'Tandatangan Pemandu', 'ஓட்டுநரின் கையொப்பம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('issued_by_code', 'Issued By', '签发人', 'Dikeluarkan oleh', 'வழங்கியவர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('first_code', 'First', '第一次', 'Kali Pertama', 'வழங்கியவர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('second_code', 'Second', '第二次', 'Kali kedua', 'இரண்டாம் முறை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_company_code', 'Company', '公司', 'Syarikat', 'நநிறுவனம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_removal_pass_no_code', 'Removal Pass No.', '移运证编号', 'Nombor Pas Memindah', 'அகற்றுதல் அனுமதிச் சீட்டு எண்ை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_license_no_code', 'License No.', '执照号码', 'Nombor Lesen', 'உரிம எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_moisture_content_code', 'Moisture Content', '含水量', 'Kandungan Kelembapan', 'ஈரப்பதம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_officer_name_code', 'Officer Name', '官员姓名', 'Nama Pegawai', 'அதிகாரியின் பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_rainbow_driver_code', 'Rainbow Driver', 'Rainbow 运输司机', 'Pemandu Rainbow', 'ஓட்டுநர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_time_in_code', 'Time In', '入场时间', 'Masa Masuk', 'உள் நுழைந்த நேரம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_time_out_code', 'Time Out', '离场时间', 'Masa Keluar', 'வெளியேறிய நேரம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_do_no_code', 'DO. No.', '送货单号', 'Nombor Pesanan Penghantaran', 'விநியோக ஒழுங்கு எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_mc_code', 'MC', 'MC', 'MC', 'MC');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_nett_weight_code', 'Nett Weight', '净重', 'Berat Bersih', 'நிகர எடை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('weight_difference_code', 'Nett Weight Difference', '净重差异', 'Perbezaan Berat Bersih', 'எடை வித்தியாசம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('raw_material_type_code', 'Raw Material Type', '原材料类型', 'Jenis Bahan Mentah', 'மூலப்பொருள் வகை');

-- 06/09/2026 --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_code', 'Company', '公司', 'Syarikat', 'நிறுவனம்');

ALTER TABLE `Weight` ADD `company_id` INT(11) NOT NULL DEFAULT '1' AFTER `id`;
ALTER TABLE `Weight_Log` ADD `company_id` INT(11) NOT NULL DEFAULT '1' AFTER `id`;
ALTER TABLE `Weight_Log` ADD `weight_id` INT(11) NOT NULL AFTER `id`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status,
        NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference,
        action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` ADD `customer_side_company` VARCHAR(100) NULL AFTER `status`, ADD `customer_side_removal_pass_no` VARCHAR(100) NULL AFTER `customer_side_company`, ADD `customer_side_license_no` VARCHAR(100) NULL AFTER `customer_side_removal_pass_no`, ADD `customer_side_moisture_content` VARCHAR(100) NULL AFTER `customer_side_license_no`, ADD `customer_side_officer_name` VARCHAR(100) NULL AFTER `customer_side_moisture_content`, ADD `customer_side_rainbow_driver` VARCHAR(100) NULL AFTER `customer_side_officer_name`, ADD `customer_side_time_in` DATETIME NULL AFTER `customer_side_rainbow_driver`, ADD `customer_side_time_out` DATETIME NULL AFTER `customer_side_time_in`, ADD `cust_side_do_no` VARCHAR(100) NULL AFTER `customer_side_time_out`, ADD `cust_side_mc` VARCHAR(100) NULL AFTER `cust_side_do_no`, ADD `cust_side_first_weight` VARCHAR(100) NULL AFTER `cust_side_mc`, ADD `cust_side_second_weight` VARCHAR(100) NULL AFTER `cust_side_first_weight`, ADD `cust_side_nett_weight` VARCHAR(100) NULL AFTER `cust_side_second_weight`, ADD `weight_difference` VARCHAR(100) NULL AFTER `cust_side_nett_weight`;

ALTER TABLE `Weight_Container_Log` ADD `customer_side_company` VARCHAR(100) NULL AFTER `status`, ADD `customer_side_removal_pass_no` VARCHAR(100) NULL AFTER `customer_side_company`, ADD `customer_side_license_no` VARCHAR(100) NULL AFTER `customer_side_removal_pass_no`, ADD `customer_side_moisture_content` VARCHAR(100) NULL AFTER `customer_side_license_no`, ADD `customer_side_officer_name` VARCHAR(100) NULL AFTER `customer_side_moisture_content`, ADD `customer_side_rainbow_driver` VARCHAR(100) NULL AFTER `customer_side_officer_name`, ADD `customer_side_time_in` DATETIME NULL AFTER `customer_side_rainbow_driver`, ADD `customer_side_time_out` DATETIME NULL AFTER `customer_side_time_in`, ADD `cust_side_do_no` VARCHAR(100) NULL AFTER `customer_side_time_out`, ADD `cust_side_mc` VARCHAR(100) NULL AFTER `cust_side_do_no`, ADD `cust_side_first_weight` VARCHAR(100) NULL AFTER `cust_side_mc`, ADD `cust_side_second_weight` VARCHAR(100) NULL AFTER `cust_side_first_weight`, ADD `cust_side_nett_weight` VARCHAR(100) NULL AFTER `cust_side_second_weight`, ADD `weight_difference` VARCHAR(100) NULL AFTER `cust_side_nett_weight`;

ALTER TABLE `Weight_Container` ADD `company_id` INT(11) NOT NULL DEFAULT '1' AFTER `id`;
ALTER TABLE `Weight_Container_Log` ADD `company_id` INT(11) NOT NULL DEFAULT '1' AFTER `id`;
ALTER TABLE `Weight_Container_Log` ADD `weight_id` INT(11) NOT NULL AFTER `id`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW 
INSERT INTO Weight_Container_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 06/09/2026 (Part 02) --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('companies_code', 'Companies', '公司', 'Syarikat-Syarikat', 'நிறுவனங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_new_reg_no_code', 'New Registration Number', '新注册号码', 'Nombor Pendaftaran Baru', 'புதிய பதிவு எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('company_code_code', 'Company Code', '公司代码', 'Kod Syarikat', 'நிறுவன குறியீடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('mobile_no_code', 'Mobile Number', '手机号码', 'Nombor Telefon Bimbit', 'மொபைல் எண்');

ALTER TABLE `Company` ADD `status` VARCHAR(10) NOT NULL DEFAULT '0' AFTER `name`;

ALTER TABLE `Company` CHANGE `modified_date` `modified_date` DATETIME on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_COMPANY` AFTER INSERT ON `Company` FOR EACH ROW INSERT INTO Company_Log (
    company_id, company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, email, mobile_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.tin_no, NEW.email, NEW.mobile_no, 1, NEW.created_by, NOW()
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_COMPANY` BEFORE UPDATE ON `Company` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Company_Log table
    INSERT INTO Company_Log (
        company_id, company_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, tin_no, email, mobile_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.tin_no, NEW.email, NEW.mobile_no, action_value, NEW.modified_by, NOW()
    );
END
$$
DELIMITER ;

UPDATE message_resource SET `en`='Wastage', `zh`='损耗', `my`='Pembaziran', `ne`='வீணாக்கல்' WHERE `message_key_code`='reduce_weight_code';

CREATE TABLE `Location` (
  `id` int(11) NOT NULL,
  `location_code` varchar(30) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `port_id` int(11) DEFAULT NULL,
  `weighing_count` int(11) NOT NULL DEFAULT 2,
  `plant_id` int(5) DEFAULT 1,
  `status` int(3) DEFAULT 0,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_by` varchar(50) DEFAULT NULL,
  `modified_date` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Location` ADD PRIMARY KEY (`id`);

ALTER TABLE `Location` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `Location_Log` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `location_code` varchar(30) NOT NULL,
  `location_name` varchar(100) NOT NULL,
  `port_id` int(11) DEFAULT NULL,
  `weighing_count` int(11) NOT NULL DEFAULT 2,
  `plant_id` int(5) DEFAULT 1,
  `action_id` int(11) DEFAULT NULL,
  `action_by` varchar(50) DEFAULT NULL,
  `event_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Location_Log` ADD PRIMARY KEY (`id`);

ALTER TABLE `Location_Log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

DELIMITER $$
CREATE TRIGGER `TRG_INS_LOCATION` AFTER INSERT ON `Location` FOR EACH ROW INSERT INTO Location_Log (
    location_id, location_code, location_name, port_id, weighing_count, plant_id, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.location_code, NEW.location_name, NEW.port_id, NEW.weighing_count, NEW.plant_id, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `TRG_UPD_LOCATION` BEFORE UPDATE ON `Location` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Location_Log table
    INSERT INTO Location_Log (
        location_id, location_code, location_name, port_id, weighing_count, plant_id, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.location_code, NEW.location_name, NEW.port_id, NEW.weighing_count, NEW.plant_id, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('locations_code', 'Locations', '位置', 'Lokasi', 'இடங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('location_code_code', 'Location Code', '位置代码', 'Kod Lokasi', 'இடத்தின் குறியீடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('location_name_code', 'Location Name', '位置名称', 'Nama Lokasi', 'இடத்தின் பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('weighing_count_code', 'Weighing Count', '称重次数', 'Kiraan Timbang', 'கூட்டல் எண்ணிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_new_location_code', 'Add New Location', '添加新位置', 'Tambah Lokasi Baru', 'புதிய இடத்தைச் சேர்');

-- 06/09/2026 (Part 03) --
ALTER TABLE `Supplier` ADD `payment_term` VARCHAR(10) NULL AFTER `tin_no`, ADD `payment_term_period` VARCHAR(10) NULL AFTER `payment_term`;
ALTER TABLE `Supplier_Log` ADD `payment_term` VARCHAR(10) NULL AFTER `tin_no`, ADD `payment_term_period` VARCHAR(10) NULL AFTER `payment_term`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SUPPLIER` AFTER INSERT ON `Supplier` FOR EACH ROW 
INSERT INTO Supplier_Log (
    supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.payment_term, NEW.payment_term_period, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SUPPLIER` BEFORE UPDATE ON `Supplier` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Supplier_Log table
    INSERT INTO Supplier_Log (
        supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.payment_term, NEW.payment_term_period, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('payment_term_code', 'Payment Term', '分期', 'Tempoh', 'காலம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('payment_term_period_code', 'Payment Term Period', '分期期限', 'Tempoh Pembayaran', 'செலுத்தல் காலம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('term_code', 'Term', '分期', 'Tempoh', 'காலம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('cash_code', 'Cash', '现金', 'Tunai', 'பணம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('daily_code', 'Daily', '每日', 'Harian', 'தினம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('weekly_code', 'Weekly', '每周', 'Mingguan', 'வாரம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('bi_weekly_code', 'Bi-Weekly', '每两周', 'Dua Mingguan', 'இரண்டு வாரங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('monthly_code', 'Monthly', '每月', 'Bulanan', 'மாதம்');

CREATE TABLE `Payment_Voucher` (
  `id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `company_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `voucher_no` varchar(100) NOT NULL,
  `voucher_date` datetime NOT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `weighing_type` varchar(30) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `unit_price` varchar(100) NOT NULL DEFAULT '0',
  `tax` varchar(3) NOT NULL DEFAULT '0',
  `total_nett_weight` varchar(100) DEFAULT NULL,
  `total_amount` varchar(100) DEFAULT NULL,
  `deduction_amount` varchar(100) DEFAULT NULL,
  `addition_amount` varchar(100) DEFAULT NULL,
  `final_amount` varchar(100) DEFAULT NULL,
  `outstanding_amount` varchar(100) DEFAULT NULL,
  `outstanding_details` text DEFAULT NULL,
  `deduction_details` text DEFAULT NULL,
  `addition_details` text DEFAULT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` varchar(50) NOT NULL,
  `deleted` int(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Payment_Voucher` ADD PRIMARY KEY (`id`);

ALTER TABLE `Payment_Voucher` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `Payment_Voucher_Log` (
  `id` int(11) NOT NULL,
  `payment_voucher_id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `company_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `voucher_no` varchar(100) NOT NULL,
  `voucher_date` datetime NOT NULL,
  `from_date` datetime DEFAULT NULL,
  `to_date` datetime DEFAULT NULL,
  `weighing_type` varchar(30) DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `unit_price` varchar(100) NOT NULL DEFAULT '0',
  `tax` varchar(3) NOT NULL DEFAULT '0',
  `total_nett_weight` varchar(100) DEFAULT NULL,
  `total_amount` varchar(100) DEFAULT NULL,
  `deduction_amount` varchar(100) DEFAULT NULL,
  `addition_amount` varchar(100) DEFAULT NULL,
  `final_amount` varchar(100) DEFAULT NULL,
  `outstanding_amount` varchar(100) DEFAULT NULL,
  `outstanding_details` text DEFAULT NULL,
  `deduction_details` text DEFAULT NULL,
  `addition_details` text DEFAULT NULL,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `Payment_Voucher_Log` ADD PRIMARY KEY (`id`);
  
ALTER TABLE `Payment_Voucher_Log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=252;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PAY` AFTER INSERT ON `Payment_Voucher` FOR EACH ROW INSERT INTO Payment_Voucher_Log (
    payment_voucher_id, company_id, type, voucher_no, supplier_id, voucher_date, from_date, to_date, weighing_type, invoice_no, unit_price, tax, total_nett_weight, total_amount, deduction_amount, addition_amount, final_amount, outstanding_amount, outstanding_details, deduction_details, addition_details, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.type, NEW.voucher_no, NEW.supplier_id, NEW.voucher_date, NEW.from_date, NEW.to_date, NEW.weighing_type, NEW.invoice_no, NEW.unit_price, NEW.tax, NEW.total_nett_weight, NEW.total_amount, NEW.deduction_amount, NEW.addition_amount, NEW.final_amount, NEW.outstanding_amount, NEW.outstanding_details, NEW.deduction_details, NEW.addition_details, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PAY` BEFORE UPDATE ON `Payment_Voucher` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if deleted = 1, set action_id to 3, otherwise set to 2
    IF NEW.deleted = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Payment_Voucher_Log table
    INSERT INTO Payment_Voucher_Log (
        payment_voucher_id, company_id, type, voucher_no, supplier_id, voucher_date, from_date, to_date, weighing_type, invoice_no, unit_price, tax, total_nett_weight, total_amount, deduction_amount, addition_amount, final_amount, outstanding_amount, outstanding_details, deduction_details, addition_details, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.type, NEW.voucher_no, NEW.supplier_id, NEW.voucher_date, NEW.from_date, NEW.to_date, NEW.weighing_type, NEW.invoice_no, NEW.unit_price, NEW.tax, NEW.total_nett_weight, NEW.total_amount, NEW.deduction_amount, NEW.addition_amount, NEW.final_amount, NEW.outstanding_amount, NEW.outstanding_details, NEW.deduction_details, NEW.addition_details, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('payment_voucher_code', 'Payment Voucher', '付款凭证', 'Voucer Pembayaran', 'செலுத்தல் வெட்டு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('accounting_code', 'Accounting', '会计', 'Perakaunan', 'கணக்குத் தொழில்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('term_supplier_code', 'Term Supplier', '分期供应商', 'Pembekal Tempoh', 'கால வழங்குநர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('cash_supplier_code', 'Cash Supplier', '现金供应商', 'Pembekal Tunai', 'பணம் வழங்குநர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('payment_voucher_details_code', 'Payment Voucher Details', '付款凭证详情', 'Perincian Voucer Pembayaran', 'செலுத்தல் வெட்டு விபரங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('voucher_date_code', 'Voucher Date', '凭证日期', 'Tarikh Voucer', 'வெட்டு தேதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('voucher_no_code', 'Voucher No', '凭证编号', 'No. Voucer', 'வெட்டு எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('outstanding_amount_code', 'Outstanding Amount', '未付款项', 'Jumlah Terutang', 'காலியான தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('invoice_no_code', 'Invoice No', '发票编号', 'No. Invois', 'விலைப்பட்டியல் எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('cut_off_code', 'Cut Off', '截止日期', 'Tarikh Tamat', 'வெட்டு தேதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('tax_code', 'Tax', '税', 'Cukai', 'வரி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_nett_weight_code', 'Total Nett Weight', '总净重', 'Jumlah Berat Bersih', 'மொத்த நிகர எடை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_amount_code', 'Total Amount', '总金额', 'Jumlah Keseluruhan', 'மொத்த தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('transaction_details_code', 'Transaction Details', '交易详情', 'Perincian Transaksi', 'பரிவர்த்தனை விபரங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('nett_amount_code', 'Nett Amount', '净额', 'Jumlah Bersih', 'நிகர தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_code', 'Total', '总计', 'Jumlah', 'மொத்தம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('deductions_code', 'Deductions', '扣除项', 'Potongan', 'கழிப்புகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('bil_code', 'No.', '编号', 'No.', 'எண்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('amount_code', 'Amount', '金额', 'Jumlah', 'தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('additions_code', 'Additions', '附加费', 'Tambahan', 'கூடுதல்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('final_amount_code', 'Final Amount', '最终金额', 'Jumlah Akhir', 'இறுதி தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('subtotal_code', 'Subtotal', '小计', 'Jumlah Sementara', 'தற்காலிக மொத்தம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('price_details_code', 'Price Details', '价格详情', 'Perincian Harga', 'விலை விபரங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('internal_code', 'Internal', '内部', 'Dalaman', 'உள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('pv_no_code', 'PV No', 'PV编号', 'No. PV', 'PV எண்');

ALTER TABLE `Weight` ADD `pv_id` INT(11) NULL AFTER `status`;
ALTER TABLE `Weight_Log` ADD `pv_id` INT(11) NULL AFTER `status`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id,
        NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference,
        action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` ADD `pv_id` INT(11) NULL AFTER `status`;
ALTER TABLE `Weight_Container_Log` ADD `pv_id` INT(11) NULL AFTER `status`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW 
INSERT INTO Weight_Container_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

CREATE TABLE `Running_No_Setup` (
  `id` int(11) NOT NULL,
  `document` varchar(100) NOT NULL,
  `document_name` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Running_No_Setup` (`id`, `document`, `document_name`, `value`) VALUES
(1, 'payment_voucher', 'PV', '1'),
(2, 'invoice_running_no', 'INV', '1');

ALTER TABLE `Running_No_Setup` ADD PRIMARY KEY (`id`);
  
ALTER TABLE `Running_No_Setup` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('select_slip_to_print_code', 'Select Slip to Print', '选择要打印的单据', 'Pilih Slip untuk Dicetak', 'அச்சிட ஸ்லிப்பை தேர்ந்தெடுக்கவும்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('slip_type_code', 'Slip Type', '单据类型', 'Jenis Slip', 'ஸ்லிப் வகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('statement_code', 'Statement', '报表', 'Penyata', 'அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('download_pdf_code', 'Download PDF', '下载PDF', 'Muat Turun PDF', 'PDF பதிவிறக்கம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('print_code', 'Print', '打印', 'Cetak', 'அச்சிடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('select_export_method_code', 'Select Export Method', '选择导出方式', 'Pilih Kaedah Eksport', 'ஏற்றுமதி முறையை தேர்ந்தெடுக்கவும்');

ALTER TABLE `Supplier` ADD `account_no` VARCHAR(30) NULL AFTER `payment_term_period`;
ALTER TABLE `Supplier_Log` ADD `account_no` VARCHAR(30) NULL AFTER `payment_term_period`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SUPPLIER` AFTER INSERT ON `Supplier` FOR EACH ROW 
INSERT INTO Supplier_Log (
    supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, account_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.payment_term, NEW.payment_term_period, NEW.account_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SUPPLIER` BEFORE UPDATE ON `Supplier` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Supplier_Log table
    INSERT INTO Supplier_Log (
        supplier_id, supplier_code, company_reg_no, new_reg_no, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, contact_name, ic_no, tin_no, payment_term, payment_term_period, account_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.supplier_code, NEW.company_reg_no, NEW.new_reg_no, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.contact_name, NEW.ic_no, NEW.tin_no, NEW.payment_term, NEW.payment_term_period, NEW.account_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('account_no_code', 'Account No', '选择导出方式', 'Pilih Kaedah Eksport', 'ஏற்றுமதி முறையை தேர்ந்தெடுக்கவும்');

ALTER TABLE Raw_Mat ADD raw_mat_type varchar(50);
ALTER TABLE Raw_Mat_Log ADD raw_mat_type varchar(50);

-- 07/09/2026 --
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_RAW_MAT` AFTER INSERT ON `Raw_Mat` FOR EACH ROW 
INSERT INTO Raw_Mat_Log (
    raw_mat_id, raw_mat_code, name, description, variance, high, low, raw_mat_type, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.raw_mat_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, NEW.raw_mat_type, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_RAW_MAT` BEFORE UPDATE ON `Raw_Mat` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Raw_Mat_Log table
    INSERT INTO Raw_Mat_Log (
        raw_mat_id, raw_mat_code, name, description, variance, high, low, raw_mat_type, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.raw_mat_code, NEW.name, NEW.description, NEW.variance, NEW.high, NEW.low, NEW.raw_mat_type, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Payment_Voucher` ADD `approval_status` VARCHAR(20) DEFAULT 'Pending' AFTER `addition_details`;
ALTER TABLE `Payment_Voucher` ADD `approval_remarks` TEXT NULL AFTER `approval_status`;
ALTER TABLE `Payment_Voucher` ADD `approved_by` INT(11) NULL AFTER `approval_remarks`;
ALTER TABLE `Payment_Voucher` ADD `approved_date` DATETIME NULL AFTER `approved_by`;

ALTER TABLE `Payment_Voucher_Log` ADD `approval_status` VARCHAR(20) DEFAULT 'Pending' AFTER `addition_details`;
ALTER TABLE `Payment_Voucher_Log` ADD `approval_remarks` TEXT NULL AFTER `approval_status`;
ALTER TABLE `Payment_Voucher_Log` ADD `approved_by` INT(11) NULL AFTER `approval_remarks`;
ALTER TABLE `Payment_Voucher_Log` ADD `approved_date` DATETIME NULL AFTER `approved_by`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PAY` AFTER INSERT ON `Payment_Voucher` FOR EACH ROW INSERT INTO Payment_Voucher_Log (
    payment_voucher_id, company_id, type, voucher_no, supplier_id, voucher_date, from_date, to_date, weighing_type, invoice_no, unit_price, tax, total_nett_weight, total_amount, deduction_amount, addition_amount, final_amount, outstanding_amount, outstanding_details, deduction_details, addition_details, approval_status, approval_remarks, approved_by, approved_date, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.type, NEW.voucher_no, NEW.supplier_id, NEW.voucher_date, NEW.from_date, NEW.to_date, NEW.weighing_type, NEW.invoice_no, NEW.unit_price, NEW.tax, NEW.total_nett_weight, NEW.total_amount, NEW.deduction_amount, NEW.addition_amount, NEW.final_amount, NEW.outstanding_amount, NEW.outstanding_details, NEW.deduction_details, NEW.addition_details, NEW.approval_status, NEW.approval_remarks, NEW.approved_by, NEW.approved_date, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PAY` BEFORE UPDATE ON `Payment_Voucher` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if deleted = 1, set action_id to 3, otherwise set to 2
    IF NEW.deleted = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Payment_Voucher_Log table
    INSERT INTO Payment_Voucher_Log (
        payment_voucher_id, company_id, type, voucher_no, supplier_id, voucher_date, from_date, to_date, weighing_type, invoice_no, unit_price, tax, total_nett_weight, total_amount, deduction_amount, addition_amount, final_amount, outstanding_amount, outstanding_details, deduction_details, addition_details, approval_status, approval_remarks, approved_by, approved_date, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.type, NEW.voucher_no, NEW.supplier_id, NEW.voucher_date, NEW.from_date, NEW.to_date, NEW.weighing_type, NEW.invoice_no, NEW.unit_price, NEW.tax, NEW.total_nett_weight, NEW.total_amount, NEW.deduction_amount, NEW.addition_amount, NEW.final_amount, NEW.outstanding_amount, NEW.outstanding_details, NEW.deduction_details, NEW.addition_details, NEW.approval_status, NEW.approval_remarks, NEW.approved_by, NEW.approved_date, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('post_to_sql_code', 'Post To SQL', '发布到SQL', 'Hantar Ke SQL', 'SQL க்கு அனுப்பு');

-- 07/09/2026 (Sky Part 02) --
UPDATE message_resource SET en = 'Sales', zh = '销售', my = 'Jualan', ne = 'விற்பனை' WHERE message_key_code = 'dispatch_code';
UPDATE message_resource SET en = 'Purchase', zh = '采购', my = 'Pembelian', ne = 'கொள்முதல்' WHERE message_key_code = 'receiving_code';

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delivery_order_code', 'Delivery Order', '送货单', 'Pesanan Penghantaran', 'டெலிவரி ஆர்டர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('goods_received_code', 'Goods Received', '收货', 'Barang Diterima', 'பொருட்கள் பெறப்பட்டது');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delivery_order_records', 'Delivery Order Records', '送货单记录', 'Rekod Pesanan Penghantaran', 'டெலிவரி ஆர்டர் பதிவுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delivery_date_code', 'Delivery Date', '送货日期', 'Tarikh Penghantaran', 'டெலிவரி தேதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_delivery_amount_code', 'Total Delivery Amount', '送货总金额', 'Jumlah Penghantaran', 'மொத்த டெலிவரி தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('primer_mover_code', 'Primer Mover', '底漆车', 'Penggerak Primer', 'பிரைமர் மூவர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('primer_mover_container_code', 'Primer Mover + Container', '底漆车 + 集装箱', 'Penggerak Primer + Kontena', 'பிரைமர் மூவர் + கொள்கலன்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('primer_mover_different_bins_code', 'Primer Mover + Different Bins', '底漆车 + 不同箱子', 'Penggerak Primer + Tong Berbeza', 'பிரைமர் மூவர் + வெவ்வேறு தொட்டிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('own_transport_code', 'Own Transport', '自有运输', 'Pengangkutan Sendiri', 'சொந்த போக்குவரத்து');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('third_party_code', 'Third Party', '第三方', 'Pihak Ketiga', 'மூன்றாம் தரப்பு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('print_template_code', 'Print Template', '打印模板', 'Templat Cetak', 'அச்சு வார்ப்புரு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('with_weight_code', 'With Weight', '含重量', 'Dengan Berat', 'எடையுடன்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('without_weight_code', 'Without Weight', '不含重量', 'Tanpa Berat', 'எடை இல்லாமல்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('fill_in_customer_side_info_code', 'Fill in Customer Side Info', '填写客户方信息', 'Isi Maklumat Pihak Pelanggan', 'வாடிக்கையாளர் தரப்பு தகவலை நிரப்பவும்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('cancellation_reason_code', 'Cancellation Reason', '取消原因', 'Sebab Pembatalan', 'ரத்து காரணம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('post_code', 'Post', '发布', 'Hantar', 'பதிவிடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delivery_order_information_code', 'Delivery Order Information', '发布', 'Hantar', 'பதிவிடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_delivery_amount_code', 'Total Delivery Amount', '发布', 'Hantar', 'பதிவிடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('so_no_code', 'S/O No', '销售订单号', 'No. S/O', 'விற்பனை ஆர்டர் எண்');

ALTER TABLE `Weight` ADD `synced` VARCHAR(3) NOT NULL DEFAULT 'N' AFTER `cancelled_reason`;
ALTER TABLE `Weight_Log` ADD `synced` VARCHAR(3) NOT NULL DEFAULT 'N' AFTER `weight_difference`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT` AFTER INSERT ON `Weight` FOR EACH ROW 
INSERT INTO Weight_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, synced, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, NEW.synced, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT` BEFORE UPDATE ON `Weight` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Log table
    INSERT INTO Weight_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, synced, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id,
        NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference,
        NEW.synced, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

ALTER TABLE `Weight_Container` ADD `synced` VARCHAR(3) NOT NULL DEFAULT 'N' AFTER `cancelled_reason`;
ALTER TABLE `Weight_Container_Log` ADD `synced` VARCHAR(3) NOT NULL DEFAULT 'N' AFTER `weight_difference`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_WEIGHT_CONTAINER` AFTER INSERT ON `Weight_Container` FOR EACH ROW 
INSERT INTO Weight_Container_Log (
    weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code, raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, synced, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, NEW.plant_code, NEW.plant_name, NEW.customer_code, NEW.customer_name, NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight, NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, NEW.synced, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_WEIGHT_CONTAINER` BEFORE UPDATE ON `Weight_Container` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Weight_Container_Log table
    INSERT INTO Weight_Container_Log (
        weight_id, company_id, transaction_id, transaction_status, weight_type, transaction_date, lorry_plate_no1, lorry_plate_no2, supplier_weight, order_weight, plant_code, plant_name, customer_code, customer_name, supplier_code, supplier_name, product_code, product_name, product_description, raw_mat_code,raw_mat_name, container_no, invoice_no, purchase_order, delivery_no, transporter_code, transporter, destination_code, destination, remarks, gross_weight1, gross_weight1_date, tare_weight1, tare_weight1_date, nett_weight1, lorry_no2_weight, empty_container2_weight, replacement_container, gross_weight2, gross_weight2_date, tare_weight2, tare_weight2_date, nett_weight2, reduce_weight, final_weight, weight_different, weight_different_perc, is_complete, is_cancel, is_approved, manual_weight, indicator_id, weighbridge_id, indicator_id_2, unit_price, sub_total, sst, total_price, status, pv_id, customer_side_company, customer_side_removal_pass_no, customer_side_license_no, customer_side_moisture_content, customer_side_officer_name, customer_side_rainbow_driver, customer_side_time_in, customer_side_time_out, cust_side_do_no, cust_side_mc, cust_side_first_weight, cust_side_second_weight, cust_side_nett_weight, weight_difference, synced, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.company_id, NEW.transaction_id, NEW.transaction_status, NEW.weight_type, NEW.transaction_date, 
        NEW.lorry_plate_no1, NEW.lorry_plate_no2, NEW.supplier_weight, NEW.order_weight, 
        NEW.plant_code, NEW.plant_name,
        NEW.customer_code, NEW.customer_name, 
        NEW.supplier_code, NEW.supplier_name, NEW.product_code, NEW.product_name, 
        NEW.product_description, NEW.raw_mat_code, NEW.raw_mat_name, 
        NEW.container_no, NEW.invoice_no, NEW.purchase_order, NEW.delivery_no, 
        NEW.transporter_code, NEW.transporter, NEW.destination_code, NEW.destination, 
        NEW.remarks, NEW.gross_weight1, NEW.gross_weight1_date, NEW.tare_weight1, 
        NEW.tare_weight1_date, NEW.nett_weight1, NEW.lorry_no2_weight, NEW.empty_container2_weight, 
        NEW.replacement_container, NEW.gross_weight2, NEW.gross_weight2_date, 
        NEW.tare_weight2, NEW.tare_weight2_date, NEW.nett_weight2, NEW.reduce_weight,
        NEW.final_weight, NEW.weight_different, NEW.weight_different_perc, NEW.is_complete, NEW.is_cancel, 
        NEW.is_approved, NEW.manual_weight, NEW.indicator_id, NEW.weighbridge_id, 
        NEW.indicator_id_2, NEW.unit_price, NEW.sub_total, NEW.sst, NEW.total_price, NEW.status, NEW.pv_id, NEW.customer_side_company, NEW.customer_side_removal_pass_no, NEW.customer_side_license_no, NEW.customer_side_moisture_content, NEW.customer_side_officer_name, NEW.customer_side_rainbow_driver, NEW.customer_side_time_in, NEW.customer_side_time_out, NEW.cust_side_do_no, NEW.cust_side_mc, NEW.cust_side_first_weight, NEW.cust_side_second_weight, NEW.cust_side_nett_weight, NEW.weight_difference, NEW.synced, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('goods_received_code', 'Goods Received', '收货', 'Barang Diterima', 'பொருட்கள் பெறப்பட்டது');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('goods_received_records_code', 'Goods Received Records', '收货记录', 'Rekod Barang Diterima', 'பொருட்கள் பெறப்பட்ட பதிவுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('received_date_code', 'Received Date', '收货日期', 'Tarikh Diterima', 'பெறப்பட்ட தேதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_received_amount_code', 'Total Received Amount', '收货总金额', 'Jumlah Diterima', 'மொத்த பெறப்பட்ட தொகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('goods_received_information_code', 'Goods Received Information', '收货信息', 'Maklumat Barang Diterima', 'பொருட்கள் பெறப்பட்ட தகவல்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('raw_material_type_code', 'Raw Material Type', '原材料类型', 'Jenis Bahan Mentah', 'மூலப்பொருள் வகை');

-- 08/09/2026 --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('export_weighing_records_code', 'Export Weighing Records', '导出称重记录', 'Eksport Rekod Timbangan', 'எடை பதிவுகளை ஏற்றுமதி செய்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('report_type_code', 'Report Type', '报告类型', 'Jenis Laporan', 'அறிக்கை வகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('summary_report_code', 'Summary Report', '汇总报告', 'Laporan Ringkasan', 'சுருக்க அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('product_report_code', 'Product Report', '产品报告', 'Laporan Produk', 'தயாரிப்பு அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('sales_purchase_report_code', 'Sales and Purchase Report', '销售与采购报告', 'Laporan Jualan dan Pembelian', 'விற்பனை மற்றும் கொள்முதல் அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('dispatch_report_code', 'Sales Report', '销售报告', 'Laporan Jualan', 'விற்பனை அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('receiving_report_code', 'Purchase Report', '采购报告', 'Laporan Pembelian', 'கொள்முதல் அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('internal_transfer_report_code', 'Transfer to Port Report', '转运至港口报告', 'Laporan Pemindahan ke Pelabuhan', 'துறைமுகத்திற்கு மாற்றம் அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('miscellaneous_report_code', 'Miscellaneous Report', '杂项报告', 'Laporan Pelbagai', 'இதர அறிக்கை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('trx_to_port_code', 'Transfer To Port', '转运至港口', 'Pemindahan ke Pelabuhan', 'துறைமுகத்திற்கு மாற்றம்');

ALTER TABLE `Plant` ADD `port` VARCHAR(5) NOT NULL DEFAULT '1' AFTER `misc`;
ALTER TABLE `Plant_Log` ADD `port` VARCHAR(5) NOT NULL DEFAULT '1' AFTER `misc`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PLANT` AFTER INSERT ON `Plant` FOR EACH ROW INSERT INTO Plant_Log (
    plant_id, plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, sales, purchase, locals, misc, port, do_no, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.plant_code, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.sales, NEW.purchase, NEW.locals, NEW.misc, NEW.port, NEW.do_no, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PLANT` BEFORE UPDATE ON `Plant` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Plant_Log table
    INSERT INTO Plant_Log (
        plant_id, plant_code, name, address_line_1, address_line_2, address_line_3, phone_no, fax_no, sales, purchase, locals, misc, port, do_no, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.plant_code, NEW.name, NEW.address_line_1, NEW.address_line_2, NEW.address_line_3, NEW.phone_no, NEW.fax_no, NEW.sales, NEW.purchase, NEW.locals, NEW.misc, NEW.port, NEW.do_no, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;

-- 08/09/2026 Sawn Timber Tables --
CREATE TABLE IF NOT EXISTS `Sawn_Timber_Species` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `created_by` varchar(50) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `modified_by` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sawn_timber_species_name` (`name`)
);

CREATE TABLE IF NOT EXISTS `Sawn_Timber_Species_Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `species_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `Sawn_Timber_Header` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `transaction_id` varchar(100) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `lot` varchar(100) NOT NULL,
  `bundle` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(10) NOT NULL DEFAULT '0',
  `created_by` varchar(50) NOT NULL,
  `created_date` datetime NOT NULL DEFAULT current_timestamp(),
  `modified_by` varchar(50) NOT NULL,
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_sawn_timber_transaction_id` (`transaction_id`)
);

CREATE TABLE IF NOT EXISTS `Sawn_Timber_Detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) NOT NULL,
  `species` varchar(255) NOT NULL,
  `thick` decimal(12,4) NOT NULL DEFAULT 0,
  `width` decimal(12,4) NOT NULL DEFAULT 0,
  `length` decimal(12,4) NOT NULL DEFAULT 0,
  `pieces` int(11) NOT NULL DEFAULT 0,
  `tons` decimal(12,4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_sawn_timber_detail_header` (`header_id`),
  CONSTRAINT `fk_sawn_timber_detail_header` FOREIGN KEY (`header_id`) REFERENCES `Sawn_Timber_Header` (`id`) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS `Sawn_Timber_Header_Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `transaction_date` datetime NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `lot` varchar(100) NOT NULL,
  `bundle` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `status` varchar(10) NOT NULL,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sawn_timber_header_log_header` (`header_id`)
);

CREATE TABLE IF NOT EXISTS `Sawn_Timber_Detail_Log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) NOT NULL,
  `header_id` int(11) NOT NULL,
  `species` varchar(255) NOT NULL,
  `thick` decimal(12,4) NOT NULL DEFAULT 0,
  `width` decimal(12,4) NOT NULL DEFAULT 0,
  `length` decimal(12,4) NOT NULL DEFAULT 0,
  `pieces` int(11) NOT NULL DEFAULT 0,
  `tons` decimal(12,4) NOT NULL DEFAULT 0,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sawn_timber_detail_log_header` (`header_id`),
  KEY `idx_sawn_timber_detail_log_detail` (`detail_id`)
);

INSERT IGNORE INTO `Sawn_Timber_Species` (`name`, `created_by`, `modified_by`) VALUES ('Others', 'system', 'system');

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SAWN_TIMBER_HEADER` AFTER INSERT ON `Sawn_Timber_Header` FOR EACH ROW
INSERT INTO Sawn_Timber_Header_Log (
    header_id, transaction_id, transaction_date, supplier, lot, bundle, remarks, status, action_id, action_by, event_date
) VALUES (
    NEW.id, NEW.transaction_id, NEW.transaction_date, NEW.supplier, NEW.lot, NEW.bundle, NEW.remarks, NEW.status, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SAWN_TIMBER_HEADER` BEFORE UPDATE ON `Sawn_Timber_Header` FOR EACH ROW
BEGIN
    DECLARE action_value INT;

    IF NEW.status = '1' AND OLD.status <> '1' THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    INSERT INTO Sawn_Timber_Header_Log (
        header_id, transaction_id, transaction_date, supplier, lot, bundle, remarks, status, action_id, action_by, event_date
    ) VALUES (
        NEW.id, NEW.transaction_id, NEW.transaction_date, NEW.supplier, NEW.lot, NEW.bundle, NEW.remarks, NEW.status, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SAWN_TIMBER_DETAIL` AFTER INSERT ON `Sawn_Timber_Detail` FOR EACH ROW
INSERT INTO Sawn_Timber_Detail_Log (
    detail_id, header_id, species, thick, width, length, pieces, tons, action_id, action_by, event_date
) VALUES (
    NEW.id, NEW.header_id, NEW.species, NEW.thick, NEW.width, NEW.length, NEW.pieces, NEW.tons, 1, COALESCE(@sawn_timber_action_by, 'system'), NOW()
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_DEL_SAWN_TIMBER_DETAIL` BEFORE DELETE ON `Sawn_Timber_Detail` FOR EACH ROW
INSERT INTO Sawn_Timber_Detail_Log (
    detail_id, header_id, species, thick, width, length, pieces, tons, action_id, action_by, event_date
) VALUES (
    OLD.id, OLD.header_id, OLD.species, OLD.thick, OLD.width, OLD.length, OLD.pieces, OLD.tons, 3, COALESCE(@sawn_timber_action_by, 'system'), NOW()
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SAWN_TIMBER_SPECIES` AFTER INSERT ON `Sawn_Timber_Species` FOR EACH ROW
INSERT INTO Sawn_Timber_Species_Log (
    species_id, name, action_id, action_by, event_date
) VALUES (
    NEW.id, NEW.name, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SAWN_TIMBER_SPECIES` BEFORE UPDATE ON `Sawn_Timber_Species` FOR EACH ROW
INSERT INTO Sawn_Timber_Species_Log (
    species_id, name, action_id, action_by, event_date
) VALUES (
    NEW.id, NEW.name, 2, NEW.modified_by, NEW.modified_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_DEL_SAWN_TIMBER_SPECIES` BEFORE DELETE ON `Sawn_Timber_Species` FOR EACH ROW
INSERT INTO Sawn_Timber_Species_Log (
    species_id, name, action_id, action_by, event_date
) VALUES (
    OLD.id, OLD.name, 3, COALESCE(@sawn_timber_species_action_by, OLD.modified_by), NOW()
)
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('sawn_timber_code', 'Sawn Timber', '方木', 'Kayu gergaji', 'அறுக்கப்பட்ட மரம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_new_entry_code', 'Add New Entry', '新增记录', 'Tambah Entri Baharu', 'புதிய பதிவைச் சேர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('lot_code', 'Lot', '批次', 'Lot', 'தொகுதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('bundle_code', 'Bundle', '捆', 'Bundle', 'பிணை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('species_code', 'Species', '物种', 'Spesies', 'வகை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('thick_code', 'Thick', '厚', 'Tebal', 'தடிமன்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('width_code', 'Width', '宽', 'Lebar', 'அகலம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('length_code', 'Length', '长', 'Panjang', 'நீளம்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('pieces_code', 'Pieces', '件数', 'Keping', 'துண்டுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('tons_code', 'Tons', '吨', 'Tan', 'டன்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_pcs_code', 'Total Pcs', '总件数', 'Jumlah Keping', 'மொத்த துண்டுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('total_tons_code', 'Total Tons', '总吨数', 'Jumlah Tan', 'மொத்த டன்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_species_code', 'Add Species', '添加物种', 'Tambah Spesies', 'வகையைச் சேர்');

-- 09/09/2026 --
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('pv_items_code', 'PV Items', '付款凭证项目', 'Item PV', 'PV பொருட்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('item_code_code', 'Item Code', '项目代码', 'Kod Item', 'பொருள் குறியீடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('item_name_code', 'Item Name', '项目名称', 'Nama Item', 'பொருள் பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('rows_code', 'Rows', '行', 'Baris', 'வரிசைகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('selected_total_code', 'Selected Total', '已选总计', 'Jumlah Dipilih', 'தேர்ந்தெடுக்கப்பட்ட மொத்தம்');

CREATE TABLE `Pv_Items` (
  `id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `status` int(1) NOT NULL DEFAULT 0,
  `created_by` varchar(50) DEFAULT NULL,
  `created_datetime` datetime DEFAULT current_timestamp(),
  `modified_by` varchar(50) DEFAULT NULL,
  `modified_datetime` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `Pv_Items` ADD PRIMARY KEY (`id`);

ALTER TABLE `Pv_Items` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `Pv_Items_Log` (
  `id` int(11) NOT NULL,
  `pv_item_id` int(11) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `action_id` int(11) NOT NULL,
  `action_by` varchar(50) NOT NULL,
  `event_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `Pv_Items_Log` ADD PRIMARY KEY (`id`);
  
ALTER TABLE `Pv_Items_Log` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_PV_ITEM` AFTER INSERT ON `Pv_Items` FOR EACH ROW INSERT INTO Pv_Items_Log (
    pv_item_id, item_code, item_name, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.item_code, NEW.item_name, 1, NEW.created_by, NEW.created_datetime
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_PV_ITEM` BEFORE UPDATE ON `Pv_Items` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Pv_Items_Log table
    INSERT INTO Pv_Items_Log (
        pv_item_id, item_code, item_name, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.item_code, NEW.item_name, action_value, NEW.modified_by, NEW.modified_datetime
    );
END
$$
DELIMITER ;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('customer_side_info_code', 'Customer Side Info', '客户端信息', 'Maklumat Pihak Pelanggan', 'ग्राहक पक्ष जानकारी');

ALTER TABLE `Sawn_Timber_Species` ADD `status` INT(1) NOT NULL DEFAULT '0' AFTER `name`;

DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_INS_SAWN_TIMBER_SPECIES` AFTER INSERT ON `Sawn_Timber_Species` FOR EACH ROW INSERT INTO Sawn_Timber_Species_Log (
    species_id, name, action_id, action_by, event_date
) 
VALUES (
    NEW.id, NEW.name, 1, NEW.created_by, NEW.created_date
)
$$
DELIMITER ;
DELIMITER $$
CREATE OR REPLACE TRIGGER `TRG_UPD_SAWN_TIMBER_SPECIES` BEFORE UPDATE ON `Sawn_Timber_Species` FOR EACH ROW BEGIN
    DECLARE action_value INT;

    -- Check if status = 1, set action_id to 3, otherwise set to 2
    IF NEW.status = 1 THEN
        SET action_value = 3;
    ELSE
        SET action_value = 2;
    END IF;

    -- Insert into Sawn_Timber_Species_Log table
    INSERT INTO Sawn_Timber_Species_Log (
        species_id, name, action_id, action_by, event_date
    ) 
    VALUES (
        NEW.id, NEW.name, action_value, NEW.modified_by, NEW.modified_date
    );
END
$$
DELIMITER ;
DELIMITER $$
DROP TRIGGER IF EXISTS TRG_DEL_SAWN_TIMBER_SPECIES;
$$
DELIMITER ;

ALTER TABLE `Sawn_Timber_Species_Log` CHANGE `event_date` `event_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `Sawn_Timber_Species` CHANGE `modified_date` `modified_date` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE `Sawn_Timber_Species` CHANGE `modified_by` `modified_by` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

-- Role & Permissions
CREATE TABLE `modules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `modules` ADD PRIMARY KEY (`id`);
ALTER TABLE `modules` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `modules` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
ALTER TABLE `permissions` ADD PRIMARY KEY (`id`);
ALTER TABLE `permissions` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `module_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('user_management_code', 'User Management', '用户管理', 'Pengurusan Pengguna', 'பயனர் மேலாண்மை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('permissions_code', 'Permissions', '权限', 'Kebenaran', 'அனுமதிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('manage_modules_code', 'Manage Modules', '权限', 'Kebenaran', 'அனுமதிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('module_records_code', 'Module Records', '模块记录', 'Rekod Modul', 'பதிவுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('module_name_code', 'Module Name', '模块名称', 'Nama Modul', 'பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('category_code', 'Category', '类别', 'Kategori', 'பிரிவு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_new_module_code', 'Add New Module', '添加新模块', 'Tambah Modul Baru', 'புதிய தொகுதி சேர்க்க');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('module_code', 'Module', '模块', 'Modul', 'தொகுதி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('insert_defaults_code', 'Insert Defaults', '插入默认值', 'Masukkan Default', 'இயல்புநிலைகளை சேர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('access_denied_code', 'Access Denied', '访问被拒绝', 'Akses Ditolak', 'அணுகல் மறுக்கப்பட்டது');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('no_permission_code', 'You do not have permission to access this page.', '您没有权限访问此页面。', 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.', 'இந்தப் பக்கத்தை அணுக உங்களுக்கு அனுமதி இல்லை.');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('back_to_weighing_code', 'Back to Weighing', '返回称重', 'Kembali ke Penimbangan', 'நிறுத்தலுக்கு திரும்பு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('permission_records_code', 'Permission Records', '权限记录', 'Rekod Kebenaran', 'அனுமதி பதிவுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delete_permission_code', 'Delete Permission', '删除权限', 'Padam Kebenaran', 'அனுமதியை நீக்கு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_permission_code', 'Add New Permission', '添加新权限', 'Tambah Kebenaran Baru', 'புதிய அனுமதி சேர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('permission_name_code', 'Permission Name', '权限名称', 'Nama Kebenaran', 'அனுமதி பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('applicable_module_code', 'Applicable Modules', '适用模块', 'Modul Berkenaan', 'பொருந்தும் தொகுதிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('all_modules_code', 'All Modules', '所有模块', 'Semua Modul', 'அனைத்து தொகுதிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('roles_code', 'Roles', '角色', 'Peranan', 'பாத்திரங்கள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('role_and_permissions_code', 'Role & Permissions', '角色与权限', 'Peranan & Kebenaran', 'பாத்திரம் & அனுமதிகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('role_records_code', 'Role Records', '角色记录', 'Rekod Peranan', 'பாத்திர பதிவுகள்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delete_role_code', 'Delete Role', '删除角色', 'Padam Peranan', 'பாத்திரத்தை நீக்கு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('add_new_role_code', 'Add New Role', '添加新角色', 'Tambah Peranan Baharu', 'புதிய பாத்திரத்தைச் சேர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('role_name_code', 'Role Name', '角色名称', 'Nama Peranan', 'பாத்திர பெயர்');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('role_code_code', 'Role Code', '角色代码', 'Kod Peranan', 'பாத்திர குறியீடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('manage_permissions_code', 'Manage Permissions', '管理权限', 'Urus Kebenaran', 'அனுமதிகளை நிர்வகி');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('no_module_found_code', 'No modules found', '未找到模块', 'Tiada modul ditemui', 'தொகுதிகள் எதுவும் கிடைக்கவில்லை');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('select_all_code', 'Select All', '全选', 'Pilih Semua', 'அனைத்தையும் தேர்ந்தெடு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('multi_roles_delete_confirmation_code', 'Are you sure you want to delete these roles?', '您确定要删除这些角色吗？', 'Adakah anda pasti mahu memadam peranan ini?', 'இந்த பாத்திரங்களை நீக்க விரும்புகிறீர்களா?');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('roles_delete_confirmation_code', 'Are you sure you want to delete this role', '您确定要删除此角色吗', 'Adakah anda pasti mahu memadam peranan ini', 'இந்த பாத்திரத்தை நீக்க விரும்புகிறீர்களா');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('min_one_role_delete_code', 'Please select at least one role to delete.', '请至少选择一个角色进行删除。', 'Sila pilih sekurang-kurangnya satu peranan untuk dipadam.', 'நீக்க குறைந்தது ஒரு பாத்திரத்தைத் தேர்ந்தெடுக்கவும்.');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('deselect_all_code', 'Deselect All', '取消全选', 'Nyahpilih Semua', 'அனைத்தையும் தேர்வுநீக்கு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('insert_default_permissions_code', 'Insert Default Permissions', '插入默认权限', 'Masukkan Kebenaran Lalai', 'இயல்புநிலை அனுமதிகளைச் செருகு');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('insert_default_permissions_warning_code', 'Insert default permissions? Existing permissions will not be affected.', '插入默认权限？现有权限不会受到影响。', 'Masukkan kebenaran lalai? Kebenaran sedia ada tidak akan terjejas.', 'இயல்புநிலை அனுமதிகளைச் செருகவா? ஏற்கனவே உள்ள அனுமதிகள் பாதிக்கப்படாது.');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('multi_delete_permissions_message_code', 'Are you sure you want to delete these permissions?', '您确定要删除这些权限吗？', 'Adakah anda pasti mahu memadamkan kebenaran ini?', 'இந்த அனுமதிகளை நிச்சயமாக நீக்க விரும்புகிறீர்களா?');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('delete_permissions_message_code', 'Are you sure you want to delete this permission?', '您确定要删除此权限吗？', 'Adakah anda pasti mahu memadamkan kebenaran ini?', 'இந்த அனுமதியை நிச்சயமாக நீக்க விரும்புகிறீர்களா?');

INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('confirm_reset_password_code', 'Are you sure you want to reset this user password to 123456?', '您确定要将此用户密码重置为123456吗？', 'Adakah anda pasti mahu menetapkan semula kata laluan pengguna ini kepada 123456?', 'இந்த பயனரின் கடவுச்சொல்லை 123456 ஆக மீட்டமைக்க விரும்புகிறீர்களா?');
INSERT INTO `message_resource` (`message_key_code`, `en`, `zh`, `my`, `ne`) VALUES ('reset_password_code', 'Reset Password', '重置密码', 'Tetapkan Semula Kata Laluan', 'கடவுச்சொல்லை மீட்டமை');
