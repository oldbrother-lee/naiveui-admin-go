package repository

import (
	"recharge-go/internal/model"

	"gorm.io/gorm"
)

type ProductRepository struct {
	db *gorm.DB
}

func NewProductRepository(db *gorm.DB) *ProductRepository {
	return &ProductRepository{db: db}
}

// List 获取商品列表
func (r *ProductRepository) List(req *model.ProductListRequest) ([]model.Product, int64, error) {
	var products []model.Product
	var total int64

	query := r.db.Model(&model.Product{}).
		Preload("Category").    // 预加载分类
		Preload("ProductType"). // 预加载商品类型
		Where("status = ?", 1)

	if req.Type > 0 {
		query = query.Where("type = ?", req.Type)
	}
	if req.Category > 0 {
		query = query.Where("category_id = ?", req.Category)
	}
	if req.ISP != "" {
		query = query.Where("isp LIKE ?", "%"+req.ISP+"%")
	}
	if req.Status > 0 {
		query = query.Where("status = ?", req.Status)
	}

	err := query.Count(&total).Error
	if err != nil {
		return nil, 0, err
	}

	err = query.Order("sort asc, id asc").
		Offset((req.Page - 1) * req.PageSize).
		Limit(req.PageSize).
		Find(&products).Error

	return products, total, err
}

// GetByID 根据ID获取商品
func (r *ProductRepository) GetByID(id int64) (*model.Product, error) {
	var product model.Product
	err := r.db.Preload("Category").
		Preload("ProductType").
		First(&product, id).Error
	if err != nil {
		return nil, err
	}
	return &product, nil
}

// Create 创建商品
func (r *ProductRepository) Create(product *model.Product) error {
	return r.db.Create(product).Error
}

// Update 更新商品
func (r *ProductRepository) Update(product *model.Product) error {
	return r.db.Save(product).Error
}

// Delete 删除商品
func (r *ProductRepository) Delete(id int64) error {
	return r.db.Delete(&model.Product{}, id).Error
}

// GetSpecs 获取商品规格
func (r *ProductRepository) GetSpecs(productID int64) ([]model.ProductSpec, error) {
	var specs []model.ProductSpec
	err := r.db.Where("product_id = ?", productID).Order("sort asc").Find(&specs).Error
	return specs, err
}

// GetGradePrices 获取商品会员价格
func (r *ProductRepository) GetGradePrices(productID int64) ([]model.ProductGradePrice, error) {
	var prices []model.ProductGradePrice
	err := r.db.Where("product_id = ?", productID).Find(&prices).Error
	return prices, err
}

// GetCategory 获取商品分类
func (r *ProductRepository) GetCategory(id int64) (*model.ProductCategory, error) {
	var category model.ProductCategory
	err := r.db.First(&category, id).Error
	if err != nil {
		return nil, err
	}
	return &category, nil
}

// ListCategories 获取商品分类列表
func (r *ProductRepository) ListCategories() ([]model.ProductCategory, error) {
	var categories []model.ProductCategory
	err := r.db.Where("status = ?", 1).Order("sort asc").Find(&categories).Error
	return categories, err
}
