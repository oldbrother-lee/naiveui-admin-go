package database

import (
	"fmt"
	"recharge-go/internal/config"
	"recharge-go/internal/model"

	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

var DB *gorm.DB

func InitDB() error {
	cfg := config.GetConfig()
	dbConfig := cfg.Database

	dsn := fmt.Sprintf("%s:%s@tcp(%s:%d)/%s?charset=utf8mb4&parseTime=True&loc=Local",
		dbConfig.User,
		dbConfig.Password,
		dbConfig.Host,
		dbConfig.Port,
		dbConfig.DBName,
	)

	var err error
	DB, err = gorm.Open(mysql.Open(dsn), &gorm.Config{})
	if err != nil {
		return err
	}

	// Auto migrate models
	err = DB.AutoMigrate(
		&model.User{},
		&model.Role{},
		&model.Permission{},
		&model.RolePermission{},
		&model.ProductType{},
		&model.ProductTypeCategory{},
		&model.Platform{},
		&model.PlatformAccount{},
		&model.PlatformAPI{},
	)
	if err != nil {
		return err
	}

	return nil
}
