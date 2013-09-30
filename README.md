Magmi Plugin For Importing Customer Group Prices (base code / forked from tim-bezhashvyly/magmi-grouped-price-plugin)
================================================

This plugin allows to import customer group prices.
The column format for customer price groups is `group_price:customer_group`, where `customer_group` is your customer group name.

Multiwebsite functionality
--------------------------

You should have a `websites` column with comma separated website codes (e.g. com,net) in your import file.

Use the same ordering in the group_price column (but with pipe (|) as separator:
e.g. `group_price_for_com_website|group_price_for_net_website`.
