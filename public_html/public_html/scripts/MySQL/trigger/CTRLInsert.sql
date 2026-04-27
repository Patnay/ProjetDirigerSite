/*=======DONNEE PAR LA PROF=======*/

drop trigger CTRLInserItem; 
DELIMITER |; 
CREATE TRIGGER CTRLInserItem BEFORE INSERT  ON Items 
FOR EACH ROW 
begin 
declare minPrix int;  
declare maxPrix int;  
 -- si l'item inséré n'est pas un sort, vérifie le prix  -- minimum d'un sort 
if(new.typeItem <>'S') Then 
 select  min(prix)  into minPrix from Items where typeItem ='S' ; 
 if(new.Prix >=minPrix) then  
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'prix doit être bas';  
 end if;     
end if; 
 -- si l'item inséré est un sort, vérifie le prix  -- maximum des autres items 
if(new.typeItem='S') Then  
 select  max(prix)  into maxPrix from Items where typeItem <>'S' ; 
 if(new.Prix <=maxPrix) then  
SIGNAL SQLSTATE '45001' SET MESSAGE_TEXT = 'prix doit être haut';  
 end if; 
end if; 
 
END |;