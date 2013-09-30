Magmi Plugin For Importing Customer Group Prices
================================================

This is a simple plugin that allows to import customer group prices.

Just copy it to your Magmi directory respecting directory structure. After that you will be able to see it in the plugins list.

The column format for customer price groups is `group_price:customer_group`, where `customer_group` is your customer group name.

Multiwebsite functionality
==========================

You should have a `websites` column with comma separated website codes (e.g. com,net) in your import file.

Use the same ordering in the group_price column:
e.g. `group_price for com website:customer_group`,`group_price for net website:customer_group`.
