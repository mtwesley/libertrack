
-- turn all barcode uniqueness warnings into errors

update errors set type = 'E' where error = 'is_valid_barcode';



