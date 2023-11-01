import { Row, Col, Nav, Tab, Badge, Button, ButtonGroup } from 'react-bootstrap';
import { Link, useNavigate } from 'react-router-dom';
import { Card } from '@/components/custom';
import React, { useEffect, useState } from 'react';
import { IMajors } from '@/models/major';
import { formatDateFromCreatedAt } from '../../blog/components/format-date';
import { QandAService } from '@/apis/services/qanda.service';
import { ListNewQAndAs } from './components/list-new-qanda';
import { ListBestCmtQAndAs } from './components/list-best-cmt-qanda';
import { ListBestLikeQAndAs } from './components/list-best-like-qanda';
import { ListNoAnswerQAndAs } from './components/list-no-answer-qanda';
import { ListMyQAndAs } from './components/list-my-qanda';

const imageUrl = 'https://picsum.photos/20';

export const ListQandAPage = ({ data }: any) => {
  const navigate = useNavigate();
  // console.log(data);

  const handleDetailsClick = (id: number) => {
    QandAService.getDetailQandA(id)
      .then(response => {
        const detailData = response.data;
        const idToPass = detailData.id;
        console.log(`Thông tin chi tiết câu hỏi ID - ${id}`);
        navigate(`/quest/${id}`);
      })
      .catch(error => {
        console.error('Error fetching details:', error);
      });
  };

  return (
    <>
      {/* Danh sách câu hỏi */}
      <Card>
        <Card.Body className="p-0">
          <div className="user-tabing">
            <Tab.Container defaultActiveKey="f1">
              <Col sm="12">
                <Card>
                  <Card.Body className="p-0">
                    <div className="user-tabing p-3">
                      <div className="d-flex flex-wrap align-items-center justify-content-between">
                        <Nav
                          variant="pills"
                          className="d-flex align-items-center text-center profile-forum-items p-0 m-0 w-100"
                        >
                          <Col sm={2} className=" p-0">
                            <Nav.Link eventKey="f1" role="button">
                              Mới nhất
                            </Nav.Link>
                          </Col>
                          <Col sm={2} className=" p-0">
                            {/* Câu trả lời tốt nhất, đáng tin nhất (Có lượt thích nhiều) */}
                            <Nav.Link eventKey="f2" role="button">
                              Hay nhất
                            </Nav.Link>
                          </Col>
                          {/* <Col sm={2} className=" p-0">
                          <Nav.Link eventKey="" role="button">Liked Topics</Nav.Link>
                          </Col> */}
                          <Col sm={2} className=" p-0">
                            <Nav.Link eventKey="f3" role="button">
                              Chưa trả lời
                            </Nav.Link>
                          </Col>
                          <Col sm={2} className=" p-0">
                            <Nav.Link eventKey="f4" role="button">
                              Nhiều Like
                            </Nav.Link>
                          </Col>
                          <Col sm={2} className=" p-0">
                            <Nav.Link eventKey="f5" role="button">
                              My Question
                            </Nav.Link>
                          </Col>
                        </Nav>
                      </div>
                    </div>
                  </Card.Body>
                </Card>
              </Col>

              <Tab.Content>
                <Tab.Pane eventKey="f1" className="fade show" id="Posts" role="tabpanel">
                  <Card>
                    <Card.Body>
                      {/* Danh sách câu hỏi mới nhất */}
                      <ListNewQAndAs data={data} />
                    </Card.Body>
                  </Card>
                </Tab.Pane>

                <Tab.Pane eventKey="f2" className="fade show" id="Photos" role="tabpanel">
                  <Card>
                    <Card.Body>
                      {/* Danh sách câu hỏi hay nhất */}
                      <ListBestCmtQAndAs />
                    </Card.Body>
                  </Card>
                </Tab.Pane>

                <Tab.Pane eventKey="f3" className="fade show" id="Abouts" role="tabpanel">
                  <Card>
                    <Card.Body>
                      {/* Danh sách câu hỏi chưa có câu trả lời */}
                      <ListNoAnswerQAndAs />
                    </Card.Body>
                  </Card>
                </Tab.Pane>

                <Tab.Pane eventKey="f4" className="fade show" id="Friends" role="tabpanel">
                  <Card>
                    <Card.Body>
                      {/* Danh sách câu hỏi nhiều like nhất */}
                      <ListBestLikeQAndAs />
                    </Card.Body>
                  </Card>
                </Tab.Pane>

                <Tab.Pane eventKey="f5" className="fade show" id="Abouts" role="tabpanel">
                  <Card>
                    <Card.Body>
                      {/* Danh sách câu hỏi của bạn */}
                      <ListMyQAndAs />
                    </Card.Body>
                  </Card>
                </Tab.Pane>
              </Tab.Content>
            </Tab.Container>
          </div>
        </Card.Body>
      </Card>
    </>
  );
};
