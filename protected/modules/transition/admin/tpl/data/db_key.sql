ALTER TABLE `product_catalog`
  ADD FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#@@#
ALTER TABLE `product_photo`
  ADD FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#@@#
ALTER TABLE `product_status_set`
  ADD CONSTRAINT FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#@@#
ALTER TABLE `ru_product_photo`
  ADD FOREIGN KEY (`photo_id`) REFERENCES `product_photo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
#@@#
ALTER TABLE `ru_product`
  ADD FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;  